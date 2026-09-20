<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Models\User;

class TikTokFollowerService
{
    private const SETTING_KEY = 'tiktok_followers';

    /**
     * @return array{value: int|null, source: string|null, updated_at: \Illuminate\Support\Carbon|null}
     */
    public function snapshot(): array
    {
        $setting = SiteSetting::query()
            ->where('key', self::SETTING_KEY)
            ->first();

        $value = $setting !== null && ctype_digit((string) $setting->value)
            ? (int) $setting->value
            : null;

        return [
            'value' => $value,
            'source' => $setting?->source,
            'updated_at' => $setting?->updated_at,
        ];
    }

    public function followers(): ?int
    {
        return $this->snapshot()['value'];
    }

    public function storeManual(int $followers, User $administrator): SiteSetting
    {
        return SiteSetting::query()->updateOrCreate(
            ['key' => self::SETTING_KEY],
            [
                'value' => (string) $followers,
                'source' => 'manual',
                'updated_by' => $administrator->id,
            ],
        );
    }
}
