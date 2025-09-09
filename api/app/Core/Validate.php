<?php
namespace App\Core;

final class Validate {
  public static function email(string $v): bool {
    return (bool) filter_var($v, FILTER_VALIDATE_EMAIL);
  }

  public static function stringLen(string $v, int $min, int $max): bool {
    $len = mb_strlen($v, 'UTF-8');
    return $len >= $min && $len <= $max;
  }

  public static function password(string $v, int $min = 8, int $max = 128): bool {
    // simple, sane bounds; you can add complexity rules later
    return self::stringLen($v, $min, $max);
  }

  public static function colorHex(?string $v): bool {
    if ($v === null) return true;
    return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $v);
  }

  public static function id($v): bool {
    return is_int($v) ? $v > 0 : (ctype_digit((string)$v) && (int)$v > 0);
  }

  public static function boolLike($v): bool {
    return is_bool($v) || $v === 0 || $v === 1 || $v === '0' || $v === '1' || $v === 'true' || $v === 'false';
  }

  public static function dateYmd(string $v): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return false;
    [$y,$m,$d] = array_map('intval', explode('-', $v));
    return checkdate($m,$d,$y);
  }

  public static function money($v): bool {
    // numeric, at most 2 decimals
    return is_numeric($v) && preg_match('/^-?\d+(\.\d{1,2})?$/', (string)$v);
  }

  public static function currency(string $v): bool {
    return (bool) preg_match('/^[A-Z]{3}$/', $v); // ISO-4217 style
  }
}
