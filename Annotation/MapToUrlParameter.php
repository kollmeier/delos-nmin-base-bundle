<?php
/**
 * Created by PhpStorm.
 * User: carstenkollmeier
 * Date: 15.11.18
 * Time: 22:25
 */

namespace App\Delos\Nmin\BaseBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class MapToUrlParameter
 * @package App\Delos\Nmin\BaseBundle\Annotation
 *
 * @Annotation\Target("PROPERTY");
 */
class MapToUrlParameter
{

    /**
     * @var string
     */
    public $default;

    /**
     * @var string
     */
    public $requirement;

    /**
     * @var boolean
     */
    public $showInUrl=true;

}