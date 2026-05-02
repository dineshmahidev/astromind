<?php

namespace App\Services;

/**
 * Agora RTC Token Builder
 * Based on Agora's official token generation algorithm.
 * Reference: https://github.com/AgoraIO/Tools/tree/master/DynamicKey/AgoraDynamicKey/php
 */
class AgoraTokenService
{
    const ROLE_PUBLISHER    = 1;
    const ROLE_SUBSCRIBER   = 2;
    const VERSION           = '006';

    public static function buildTokenWithUid(
        string $appId,
        string $appCertificate,
        string $channelName,
        int $uid,
        int $role,
        int $privilegeExpiredTs
    ): string {
        return self::buildToken($appId, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
    }

    private static function buildToken(
        string $appId,
        string $appCertificate,
        string $channelName,
        int $uid,
        int $role,
        int $privilegeExpiredTs
    ): string {
        $timestamp   = time();
        $randomInt   = rand(1, 99999999);
        $uidStr      = $uid === 0 ? '' : (string) $uid;

        // Privileges
        $privileges = [
            1 => $privilegeExpiredTs, // joinChannel
            2 => $privilegeExpiredTs, // publishAudioStream
            3 => $privilegeExpiredTs, // publishVideoStream
            4 => $privilegeExpiredTs, // publishDataStream
        ];

        // Pack message
        $msg = self::packUint16(1) .           // version
               self::packUint32($randomInt) .
               self::packUint32($timestamp) .
               self::packMapUint32($privileges);

        // Signature
        $rawHmacContent = $appId . $channelName . $uidStr . $msg;
        $signature = hash_hmac('sha256', $rawHmacContent, $appCertificate, true);

        // Token content
        $tokenContent = $signature . self::packString($appId) . self::packString($channelName) . self::packString($uidStr) . $msg;

        return self::VERSION . $appId . base64_encode($tokenContent);
    }

    private static function packUint16(int $x): string
    {
        return pack('n', $x);
    }

    private static function packUint32(int $x): string
    {
        return pack('N', $x);
    }

    private static function packString(string $s): string
    {
        return pack('n', strlen($s)) . $s;
    }

    private static function packMapUint32(array $map): string
    {
        ksort($map);
        $result = pack('n', count($map));
        foreach ($map as $key => $value) {
            $result .= self::packUint16($key);
            $result .= self::packUint32($value);
        }
        return $result;
    }
}
