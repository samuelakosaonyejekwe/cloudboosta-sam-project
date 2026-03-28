<?php

class Validator {
  public static function requireString(array $arr, string $key, int $maxLen = 255): string {
    if (!isset($arr[$key])) {
      throw new InvalidArgumentException("Missing field: $key");
    }
    $val = trim((string)$arr[$key]);
    if ($val === '') {
      throw new InvalidArgumentException("Empty field: $key");
    }
    if (mb_strlen($val) > $maxLen) {
      throw new InvalidArgumentException("Field too long: $key");
    }
    return $val;
  }

  public static function optionalString(array $arr, string $key, int $maxLen = 255): ?string {
    if (!isset($arr[$key])) return null;
    $val = trim((string)$arr[$key]);
    if ($val === '') return null;
    if (mb_strlen($val) > $maxLen) {
      throw new InvalidArgumentException("Field too long: $key");
    }
    return $val;
  }

  public static function requireInt(array $arr, string $key, int $min = 1, int $max = 1000000): int {
    if (!isset($arr[$key])) {
      throw new InvalidArgumentException("Missing field: $key");
    }
    if (!is_numeric($arr[$key])) {
      throw new InvalidArgumentException("Invalid number: $key");
    }
    $val = (int)$arr[$key];
    if ($val < $min || $val > $max) {
      throw new InvalidArgumentException("Out of range: $key");
    }
    return $val;
  }

  public static function requireEmail(array $arr, string $key): string {
    $email = self::requireString($arr, $key, 255);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new InvalidArgumentException("Invalid email: $key");
    }
    return $email;
  }
}
