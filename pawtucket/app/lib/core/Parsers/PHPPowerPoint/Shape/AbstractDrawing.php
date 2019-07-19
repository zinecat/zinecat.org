<?php
/**
 * This file is part of PHPPowerPoint - A pure PHP library for reading and writing
 * presentations documents.
 *
 * PHPPowerPoint is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @link        https://github.com/PHPOffice/PHPPowerPoint
 * @copyright   2009-2014 PHPPowerPoint contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpPowerpoint\Shape;

use PhpOffice\PhpPowerpoint\AbstractShape;
use PhpOffice\PhpPowerpoint\ComparableInterface;

/**
 * Abstract drawing
 */
abstract class AbstractDrawing extends AbstractShape implements ComparableInterface
{
    /**
     * Image counter
     *
     * @var int
     */
    private static $imageCounter = 0;

    /**
     * Image index
     *
     * @var int
     */
    private $imageIndex = 0;

    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Description
     *
     * @var string
     */
    protected $description;

    /**
     * Proportional resize
     *
     * @var boolean
     */
    protected $resizeProportional;

    /**
     * Slide relation ID (should not be used by user code!)
     *
     * @var string
     */
    public $relationId = null;

    /**
     * Create a new \PhpOffice\PhpPowerpoint\Slide\AbstractDrawing
     */
    public function __construct()
    {
        // Initialise values
        $this->name               = '';
        $this->description        = '';
        $this->resizeProportional = true;

        // Set image index
        self::$imageCounter++;
        $this->imageIndex = self::$imageCounter;

        // Initialize parent
        parent::__construct();
    }

    /**
     * Get image index
     *
     * @return int
     */
    public function getImageIndex()
    {
        return $this->imageIndex;
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set Name
     *
     * @param  string                          $pValue
     * @return \PhpOffice\PhpPowerpoint\Shape\AbstractDrawing
     */
    public function setName($pValue = '')
    {
        $this->name = $pValue;
        return $this;
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set Description
     *
     * @param  string                          $pValue
     * @return \PhpOffice\PhpPowerpoint\Shape\AbstractDrawing
     */
    public function setDescription($pValue = '')
    {
        $this->description = $pValue;

        return $this;
    }

    /**
     * Set Width
     *
     * @param  int                             $pValue
     * @return \PhpOffice\PhpPowerpoint\Shape\AbstractDrawing
     */
    public function setWidth($pValue = 0)
    {
        // Resize proportional?
        if ($this->resizeProportional && $pValue != 0) {
            $ratio         = $this->height / $this->width;
            $this->height = (int) round($ratio * $pValue);
        }

        // Set width
        $this->width = $pValue;

        return $this;
    }

    /**
     * Set Height
     *
     * @param  int                             $pValue
     * @return \PhpOffice\PhpPowerpoint\Shape\AbstractDrawing
     */
    public function setHeight($pValue = 0)
    {
        // Resize proportional?
        if ($this->resizeProportional && $pValue != 0) {
            $ratio        = $this->width / $this->height;
            $this->width = (int) round($ratio * $pValue);
        }

        // Set height
        $this->height = $pValue;

        return $this;
    }

    /**
     * Set width and height with proportional resize
     * @author Vincent@luo MSN:kele_100@hotmail.com
     * @param  int                             $width
     * @param  int                             $height
     * @example $objDrawing->setResizeProportional(true);
     * @example $objDrawing->setWidthAndHeight(160,120);
     * @return \PhpOffice\PhpPowerpoint\Shape\AbstractDrawing
     */
    public function setWidthAndHeight($width = 0, $height = 0)
    {
        $xratio = $width / $this->width;
        $yratio = $height / $this->height;
        if ($this->resizeProportional && !($width == 0 || $height == 0)) {
            if (($xratio * $this->height) < $height) {
                $this->height = (int) ceil($xratio * $this->height);
                $this->width  = $width;
            } else {
                $this->width  = (int) ceil($yratio * $this->width);
                $this->height = $height;
            }
        }

        return $this;
    }

    /**
     * Get ResizeProportional
     *
     * @return boolean
     */
    public function isResizeProportional()
    {
        return $this->resizeProportional;
    }

    /**
     * Set ResizeProportional
     *
     * @param  boolean                         $pValue
     * @return \PhpOffice\PhpPowerpoint\Shape\AbstractDrawing
     */
    public function setResizeProportional($pValue = true)
    {
        $this->resizeProportional = $pValue;

        return $this;
    }

    /**
     * Get hash code
     *
     * @return string Hash code
     */
    public function getHashCode()
    {
        return md5($this->name . $this->description . parent::getHashCode() . __CLASS__);
    }
}
