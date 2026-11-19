<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MemberExcelChunkImport implements ToCollection, WithHeadingRow, WithChunkReading
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

    private int $currentRowNumber = 1; // Baris 1 adalah header

    public int $totalRows = 0;

    public int $validCount = 0;

    public int $invalidCount = 0;

    public int $importedMembersCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $previewRows = [];

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

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows): void
    {
        $parsedChunk = [];

        foreach ($rows as $row) {
            $this->currentRowNumber++;

            if ($row->filter(fn ($val) => $val !== null && trim((string) $val) !== '')->isEmpty()) {
                continue;
            }

            $this->totalRows++;
            $evaluated = $this->evaluateRow($row->toArray(), $this->currentRowNumber);

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
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function evaluateRow(array $raw, int $rowNumber): array
    {
        $errors = [];

        $memberCode = mb_strtoupper(trim((string) (
            $raw['kode_anggota']
            ?? $raw['member_code']
            ?? $raw['nomor_anggota']
            ?? $raw['nim']
            ?? $raw['nip']
            ?? ''
        )));
        $name = trim((string) ($raw['nama'] ?? $raw['name'] ?? $raw['nama_lengkap'] ?? ''));
        $email = mb_strtolower(trim((string) ($raw['email'] ?? '')));
        $phone = trim((string) ($raw['no_hp'] ?? $raw['phone'] ?? $raw['telepon'] ?? $raw['hp'] ?? ''));
        $passwordRaw = trim((string) ($raw['password'] ?? ''));

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
