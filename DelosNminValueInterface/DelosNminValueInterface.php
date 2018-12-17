<?php
/**
 * Created by PhpStorm.
 * User: carstenkollmeier
 * Date: 15.11.18
 * Time: 15:03
 */

namespace App\Delos\Nmin\BaseBundle\DelosNminValueInterface;


interface DelosNminValueInterface
{
    public static function isAccessable(): bool;
    public static function getClasstype(?bool $prefixed = false) : ?string;
    public static function getBaseUrl (): string;
}