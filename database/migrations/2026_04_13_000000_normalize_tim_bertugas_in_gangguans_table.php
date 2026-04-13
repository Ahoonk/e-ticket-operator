<?php

use App\Models\Anggota;
use App\Models\Gangguan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private function normalizeKey(string $value): string
    {
        return preg_replace('/\s+/', '', Str::lower(trim($value))) ?? '';
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (
            !Schema::hasTable('gangguans') ||
            !Schema::hasTable('anggotas') ||
            !Schema::hasTable('users')
        ) {
            return;
        }

        $memberIndex = Anggota::query()
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'user');
            })
            ->get()
            ->mapWithKeys(function (Anggota $anggota) {
                $name = $anggota->user?->name ?? $anggota->nama;
                $userId = $anggota->user_id ?? $anggota->user?->id;

                return [$this->normalizeKey((string) $name) => [
                    'name' => trim((string) $name),
                    'user_id' => $userId,
                ]];
            });

        Gangguan::query()
            ->whereNotNull('tim_bertugas')
            ->orderBy('id')
            ->chunkById(100, function ($gangguans) use ($memberIndex) {
                foreach ($gangguans as $gangguan) {
                    $tokens = collect(explode(',', (string) $gangguan->tim_bertugas))
                        ->map(fn ($value) => trim($value))
                        ->filter()
                        ->map(function (string $token) use ($memberIndex) {
                            if (str_contains($token, '::')) {
                                return $token;
                            }

                            $key = $this->normalizeKey($token);
                            $member = $memberIndex->get($key);

                            if (!$member || empty($member['user_id'])) {
                                return $token;
                            }

                            return $member['name'].'::'.$member['user_id'];
                        })
                        ->unique()
                        ->values()
                        ->implode(', ');

                    if ($tokens !== (string) $gangguan->tim_bertugas) {
                        $gangguan->update(['tim_bertugas' => $tokens]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('gangguans')) {
            return;
        }

        Gangguan::query()
            ->whereNotNull('tim_bertugas')
            ->orderBy('id')
            ->chunkById(100, function ($gangguans) {
                foreach ($gangguans as $gangguan) {
                    $tokens = collect(explode(',', (string) $gangguan->tim_bertugas))
                        ->map(fn ($value) => trim($value))
                        ->filter()
                        ->map(function (string $token) {
                            if (!str_contains($token, '::')) {
                                return $token;
                            }

                            return trim(explode('::', $token, 2)[0]);
                        })
                        ->unique()
                        ->values()
                        ->implode(', ');

                    if ($tokens !== (string) $gangguan->tim_bertugas) {
                        $gangguan->update(['tim_bertugas' => $tokens]);
                    }
                }
            });
    }
};
