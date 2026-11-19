<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MemberExcelChunkImport implements ToCollection, WithChunkReading, WithMultipleSheets
{
    private bool $commit;

    /** @var array<string, bool> */
    private array $existingEmails = [];

    /** @var array<string, bool> */
    private array $existingMemberCodes = [];

    /** @var array<string, bool> */
    private array $seenEmails = [];

    /** @var array<string, bool> */
    private array $seenMemberCodes = [];

    private int $currentRowNumber = 0;

    private bool $headerDetected = false;

    /** @var array<int, string> */
    private array $headerMap = [];

    private bool $hasMemberCodeCol = false;

    private bool $hasEmailCol = false;

    private int $autoCodeSequence = 1;

    public int $totalRows = 0;

    public int $validCount = 0;

    public int $invalidCount = 0;

    public int $importedMembersCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $previewRows = [];

    /** @var array<string, array<int, string>> */
    private const HEADER_ALIASES = [
        'kode_anggota' => [
            'kode_anggota', 'member_code', 'nomor_anggota', 'no_anggota', 'id_anggota',
            'nim', 'nip', 'nik', 'nis', 'npm', 'id_member', 'student_id', 'employee_id', 'kode',
        ],
        'nama' => [
            'nama', 'name', 'nama_lengkap', 'full_name', 'nama_anggota',
            'nama_mahasiswa', 'nama_pegawai', 'member_name', 'anggota', 'mahasiswa',
        ],
        'email' => [
            'email', 'e_mail', 'alamat_email', 'email_address', 'surel', 'mail',
        ],
        'no_hp' => [
            'no_hp', 'phone', 'telepon', 'hp', 'no_telp', 'nomor_hp', 'nomor_telepon',
            'whatsapp', 'wa', 'no_wa', 'mobile', 'phone_number', 'telp', 'kontak',
        ],
        'password' => [
            'password', 'kata_sandi', 'sandi', 'pass', 'pin',
        ],
    ];

    public function __construct(bool $commit = false)
    {
        $this->commit = $commit;

        $this->existingEmails = User::pluck('email')
            ->mapWithKeys(fn ($email) => [mb_strtolower(trim((string) $email)) => true])
            ->all();

        $this->existingMemberCodes = User::whereNotNull('member_code')
            ->pluck('member_code')
            ->mapWithKeys(fn ($code) => [mb_strtoupper(trim((string) $code)) => true])
            ->all();
    }

    public function sheets(): array
    {
        return [0 => $this];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows): void
    {
        $parsedChunk = [];

        foreach ($rows as $row) {
            $this->currentRowNumber++;
            $rawArr = $row->toArray();

            $nonEmpty = array_filter($rawArr, fn ($val) => $val !== null && trim((string) $val) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $hasStringKeys = count(array_filter(array_keys($rawArr), 'is_string')) > 0;

            if ($hasStringKeys) {
                $mappedRaw = $this->mapAssociativeRow($rawArr);
                $this->hasMemberCodeCol = array_key_exists('kode_anggota', $mappedRaw);
                $this->hasEmailCol = array_key_exists('email', $mappedRaw);
            } else {
                $cells = array_values($rawArr);

                if (! $this->headerDetected) {
                    $detectedMap = $this->detectHeaderMap($cells);
                    if (count($detectedMap) >= 2) {
                        $this->headerDetected = true;
                        $this->headerMap = $detectedMap;
                        $this->hasMemberCodeCol = in_array('kode_anggota', $detectedMap, true);
                        $this->hasEmailCol = in_array('email', $detectedMap, true);
                        continue;
                    }

                    if ($this->currentRowNumber <= 15) {
                        continue;
                    }

                    $this->headerDetected = true;
                    $this->headerMap = [
                        0 => 'kode_anggota',
                        1 => 'nama',
                        2 => 'email',
                        3 => 'no_hp',
                        4 => 'password',
                    ];
                    $this->hasMemberCodeCol = true;
                    $this->hasEmailCol = true;
                }

                $mappedRaw = $this->mapCellsToFields($cells);
            }

            $this->totalRows++;
            $evaluated = $this->evaluateRow($mappedRaw, $this->currentRowNumber);

            if ($evaluated['is_valid']) {
                $this->validCount++;
                $parsedChunk[] = $evaluated;
            } else {
                $this->invalidCount++;
            }

            if (! $this->commit && count($this->previewRows) < 100) {
                $this->previewRows[] = $evaluated;
            }
        }

        if ($this->commit && ! empty($parsedChunk)) {
            $this->persistChunk($parsedChunk);
        }
    }

    /**
     * @param  array<string, mixed>  $rawArr
     * @return array<string, mixed>
     */
    private function mapAssociativeRow(array $rawArr): array
    {
        $mapped = [];
        foreach ($rawArr as $key => $val) {
            $slug = $this->normalizeHeaderSlug((string) $key);
            $canonical = $slug;

            foreach (self::HEADER_ALIASES as $target => $aliases) {
                if (in_array($slug, $aliases, true)) {
                    $canonical = $target;
                    break;
                }
            }

            if (! array_key_exists($canonical, $mapped)) {
                $mapped[$canonical] = $val;
            }
        }

        return $mapped;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<int, string>
     */
    private function detectHeaderMap(array $cells): array
    {
        $map = [];
        $usedCanonical = [];

        foreach ($cells as $colIndex => $cellValue) {
            $slug = $this->normalizeHeaderSlug((string) $cellValue);
            if ($slug === '') {
                continue;
            }

            foreach (self::HEADER_ALIASES as $canonical => $aliases) {
                if (isset($usedCanonical[$canonical])) {
                    continue;
                }
                if (in_array($slug, $aliases, true)) {
                    $map[(int) $colIndex] = $canonical;
                    $usedCanonical[$canonical] = true;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<string, mixed>
     */
    private function mapCellsToFields(array $cells): array
    {
        $result = [];
        foreach ($this->headerMap as $colIndex => $canonical) {
            $result[$canonical] = $cells[$colIndex] ?? null;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function evaluateRow(array $raw, int $rowNumber): array
    {
        $errors = [];

        $memberCode = mb_strtoupper(preg_replace('/\s+/', '', trim((string) ($raw['kode_anggota'] ?? ''))) ?? '');
        $name = trim((string) (preg_replace('/\s+/', ' ', (string) ($raw['nama'] ?? '')) ?? ''));
        $email = mb_strtolower(preg_replace('/\s+/', '', trim((string) ($raw['email'] ?? ''))) ?? '');
        $phone = $this->normalizePhone($raw['no_hp'] ?? null);
        $passwordRaw = trim((string) ($raw['password'] ?? ''));

        if ($memberCode === '' && ! $this->hasMemberCodeCol && $name !== '') {
            $memberCode = $this->generateUniqueMemberCode();
        }

        if ($memberCode === '') {
            $errors[] = 'kode_anggota wajib diisi';
        } elseif (mb_strlen($memberCode) > 30) {
            $errors[] = 'kode_anggota maksimal 30 karakter';
        } elseif (isset($this->existingMemberCodes[$memberCode])) {
            $errors[] = "kode_anggota '{$memberCode}' sudah terdaftar";
        } elseif (isset($this->seenMemberCodes[$memberCode])) {
            $errors[] = "kode_anggota '{$memberCode}' duplikat di file";
        }

        if ($name === '') {
            $errors[] = 'nama wajib diisi';
        } elseif (mb_strlen($name) > 255) {
            $errors[] = 'nama maksimal 255 karakter';
        }

        if ($email === '' && ! $this->hasEmailCol && $memberCode !== '') {
            $safeSlug = mb_strtolower(preg_replace('/[^a-z0-9]/i', '', $memberCode) ?? 'member');
            $email = "{$safeSlug}@member.polmart.local";
        }

        if ($email === '') {
            $errors[] = 'email wajib diisi';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "format email '{$email}' tidak valid";
        } elseif (isset($this->existingEmails[$email])) {
            $errors[] = "email '{$email}' sudah terdaftar";
        } elseif (isset($this->seenEmails[$email])) {
            $errors[] = "email '{$email}' duplikat di file";
        }

        if ($phone !== '' && mb_strlen($phone) > 20) {
            $errors[] = 'no_hp maksimal 20 karakter';
        }

        $isValid = empty($errors);

        if ($isValid) {
            $this->seenMemberCodes[$memberCode] = true;
            $this->seenEmails[$email] = true;
        }

        return [
            'row_number' => $rowNumber,
            'is_valid' => $isValid,
            'errors' => $errors,
            'member_code' => $memberCode,
            'name' => $name,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'password_plain' => $passwordRaw !== '' ? $passwordRaw : 'password',
        ];
    }

    private function generateUniqueMemberCode(): string
    {
        do {
            $candidate = sprintf('MBR-%s-%04d', date('ym'), $this->autoCodeSequence++);
        } while (isset($this->existingMemberCodes[$candidate]) || isset($this->seenMemberCodes[$candidate]));

        return $candidate;
    }

    private function normalizePhone(mixed $value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '';
        }

        $cleaned = preg_replace('/[\s\-\(\)\.]+/', '', trim((string) $value)) ?? '';
        $cleaned = ltrim($cleaned, '+');

        if (str_starts_with($cleaned, '62') && strlen($cleaned) >= 10) {
            $cleaned = '0' . substr($cleaned, 2);
        } elseif (preg_match('/^8\d{8,12}$/', $cleaned)) {
            $cleaned = '0' . $cleaned;
        }

        return $cleaned;
    }

    private function normalizeHeaderSlug(string $header): string
    {
        $slug = mb_strtolower(trim($header));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug) ?? '';

        return trim($slug, '_');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function persistChunk(array $rows): void
    {
        DB::transaction(function () use ($rows) {
            $now = now();

            foreach ($rows as $row) {
                User::create([
                    'member_code' => $row['member_code'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'role' => 'member',
                    'password' => Hash::make($row['password_plain']),
                    'email_verified_at' => $now,
                ]);

                $this->existingMemberCodes[$row['member_code']] = true;
                $this->existingEmails[$row['email']] = true;
                $this->importedMembersCount++;
            }
        });
    }
}