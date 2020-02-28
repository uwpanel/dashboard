<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Date
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Class for handling dates
 *
 * @category   Kumbia
 * @package    Date
 * @deprecated 0.9 use native class
 */
class Date
{
    /**
     * Internal date value
     *
     * @var string
     */
    private $date;
    /**
     * Internal Value of the Day
     *
     * @var int|string
     */
    private $day;
    /**
     * Internal Value of the Year
     *
     * @var int|string
     */
    private $year;
    /**
     * Internal Value of the Month
     *
     * @var int|string
     */
    private $month;
    /**
     * Internal Value of the Month
     *
     * @var int
     */
    private $timestamp;

    /**
     * Create a date object Date
     *
     */
    public function __construct($date = "")
    {
        if ($date) {
            $date_parts      = explode("-", $date);
            $this->year      = (int) $date_parts[0];
            $this->month     = (int) $date_parts[1];
            $this->day       = (int) $date_parts[2];
            $this->date      = $date;
            $this->timestamp = mktime(0, 0, 0, $this->month, $this->day, $this->year);
        } else {
            $this->year      = date("Y");
            $this->month     = date("m");
            $this->day       = date("d");
            $this->timestamp = time();
            $this->date      = $this->year . "-" . sprintf("%02s", $this->month) . "-" . sprintf("%02s", $this->day);
        }
    }

    /**
     * Returns the name of the month of the internal date
     *
     * @return string
     */
    public function getMonthName()
    {
        return ucfirst(strftime("%B", $this->timestamp));
    }

    /**
     * Returns the abbreviated name of the month of the internal date
     *
     * @return string
     */
    public function getAbrevMonthName()
    {
        return ucfirst(strftime("%b", $this->timestamp));
    }

    /**
     * Returns the internal day of the date
     *
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Returns the internal month of the date
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Returns the internal # of the date
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Returns the timestamp of the internal date
     *
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Return the date in string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->date;
    }

    /**
     * Add months to the internal date
     *
     */
    public function addMonths($month)
    {
        if ($this->month + $month > 12) {
            $this->month = ($month % 12) + 1;
            $this->year += ((int) ($month / 12));
        } else {
            $this->month++;
        }
        $this->date = $this->year . "-" . sprintf("%02s", $this->month) . "-" . sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Subtract months from the internal date
     *
     */
    public function diffMonths($month)
    {
        if ($this->month - $month < 1) {
            $this->month = 12 - (($month % 12) + 1);
            $this->year -= ((int) ($month / 12));
        } else {
            $this->month--;
        }
        $this->date = $this->year . "-" . sprintf("%02s", $this->month) . "-" . sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Sum number days to the current date
     *
     * @param integer $days
     * @return string
     */
    public function addDays($days)
    {
        $this->date = date("Y-m-d", $this->timestamp + $days * 86400);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Subtract number days from the current date
     *
     * @param integer $days
     * @return string
     */
    public function diffDays($days)
    {
        $this->date = date("Y-m-d", $this->timestamp - $days * 86400);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Add a number of years to the internal date
     *
     * @param numeric $years
     * @return string
     */
    public function addYears($years)
    {
        $this->year += $years;
        $this->date = $this->year . "-" . sprintf("%02s", $this->month) . "-" . sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Subtract a number of years from the internal date
     *
     * @param numeric $years
     * @return string
     */
    public function diffYears($years)
    {
        $this->year -= $years;
        $this->date = $this->year . "-" . sprintf("%02s", $this->month) . "-" . sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Get using a format
     *
     * @param $format
     */
    public function getUsingFormat($format)
    {
        $datetime = new DateTime($this->date);
        return $datetime->format($format);
    }

    /**
     * Returns the name of the day of the week
     *
     * @return string
     */
    public function getDayOfWeek()
    {
        $datetime = new DateTime($this->date);
        return $datetime->format("l");
    }

    /**
     * Subtract one date from another
     *
     */
    public function diffDate($date)
    {
        $date_parts = explode("-", $date);
        $year       = (int) $date_parts[0];
        $month      = (int) $date_parts[1];
        $day        = (int) $date_parts[2];
        $timestamp  = mktime(0, 0, 0, $month, $day, $year);
        return (int) (($this->timestamp - $timestamp) / 86400);
    }

    /**
     * Returns true if the internal date is today
     *
     * @return boolean
     */
    public function isToday()
    {
        if ($this->date == date("Y-m-d")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if the internal date is yesterday
     *
     * @return boolean
     */
    public function isYesterday()
    {
        $time = mktime(0, 0, 0, date("m"), date("d"), date("Y")) - 86400;

        if ($this->timestamp == $time) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns true if the internal date is tomorrow
     *
     * @return boolean
     */
    public function isTomorrow()
    {
        $time = mktime(0, 0, 0, date("m"), date("d"), date("Y")) + 86400;

        if ($this->timestamp == $time) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Consolidate the internal values of the date
     *
     */
    private function consolideDate()
    {
        $date_parts      = explode("-", $this->date);
        $this->year      = (int) $date_parts[0];
        $this->month     = (int) $date_parts[1];
        $this->day       = (int) $date_parts[2];
        $this->timestamp = mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }
}
