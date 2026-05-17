<?php

namespace App\Services\Concerns;

/**
 * Pream's note for the team:
 * Single shared implementation of "read JSON file with shared lock, write JSON
 * file with exclusive lock". Pulled out of every Repository so the locking
 * pattern is correct in one place — no chance of one repo silently using a
 * non-locked read.
 *
 * LOCK_SH on read prevents the half-written JSON race when a writer is mid-flight.
 * LOCK_EX on write is already what we had — kept the same.
 */
trait JsonStore
{
    /** Read JSON from $path, returning [] if missing or unparseable. */
    protected function jsonRead(string $path): array
    {
        if (!file_exists($path)) return [];
        $fp = @fopen($path, 'r');
        if (!$fp) return [];
        try {
            // Shared lock: many readers, blocks while a writer holds LOCK_EX.
            if (!flock($fp, LOCK_SH)) return [];
            $raw = stream_get_contents($fp);
            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }
        $data = json_decode($raw ?: '[]', true);
        return is_array($data) ? $data : [];
    }

    /** Write JSON to $path atomically (LOCK_EX + JSON_PRETTY_PRINT). */
    protected function jsonWrite(string $path, array $data): void
    {
        @mkdir(dirname($path), 0775, true);
        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }
}
