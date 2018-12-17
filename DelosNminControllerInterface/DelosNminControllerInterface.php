<?php
/**
 * Created by PhpStorm.
 * User: carstenkollmeier
 * Date: 16.11.18
 * Time: 10:17
 */

namespace App\Delos\Nmin\BaseBundle\DelosNminControllerInterface;


use App\Delos\Nmin\BaseBundle\DelosNminValueInterface\DelosNminValueInterface;
use Symfony\Component\HttpFoundation\Response;

interface DelosNminControllerInterface
{
    public static function getMethods(): array;

    public static function getRequirements(): array;

    public static function getPriority(): int;

    public static function getCatchAll(): ?string;

    public static function getUrlPrefix(?string $delimiter, ?bool $recurse): string;
}