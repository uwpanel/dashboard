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
 * @package    ActiveRecord
 * @subpackage Behaviors
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Component to page.
 *
 * Allows paging arrays and models
 *
 * @category   Kumbia
 * @package    ActiveRecord
 * @subpackage Behaviors
 */
class Paginator
{
    /**
     * pager
     *
     * page: page number to display (default page 1)
     * per_page: number of records per page (default 10 records per page)
     *
     * For page by array:
     *  Nameless parameters in order:
     *    Parameter1: array to be paged
     *
     * For model page:
     *  Nameless parameters in order:
     *   Parameter1: model name or model object
     *   Parameter2: search condition
     *
     * Named parameters:
     *  conditions: search condition
     *  order: ordering
     *  columns: columns to display
     *
     * Returns a PageObject that has the following attributes:
     *  next: next page number, if there is no next page then it is FALSE
     *  prev: previous page number, if there is no previous page then it is FALSE
     *  current: current page number
     *  total: total pages that can be displayed
     *  items: array of page records
     *  count: Total records
     *  per_page: number of records per page
     *
     * @example
     *  $page = paginate($array, 'per_page: 5', "page: $page_num"); <br>
     *  $page = paginate('user', 'per_page: 5', "page: $page_num"); <br>
     *  $page = paginate('user', 'sexo="F"' , 'per_page: 5', "page: $page_num"); <br>
     *  $page = paginate('Usuario', 'sexo="F"' , 'per_page: 5', "page: $page_num"); <br>
     *  $page = paginate($this->Usuario, 'conditions: sexo="F"' , 'per_page: 5', "page: $page_num"); <br>
     *
     * @params object $model
     *
     * @return stdClass
     * */
    public static function paginate($model)
    {
        $params = Util::getParams(func_get_args());
        $page_number = isset($params['page']) ? (int) $params['page'] : 1;
        $per_page = isset($params['per_page']) ? (int) $params['per_page'] : 10;
        //If the page or per page is less than 1 (0 or negative)
        if ($page_number < 1 && $per_page < 1) {
            throw new KumbiaException("The page $page_number does not exist on the page");
        }
        $start = $per_page * ($page_number - 1);
        //Instance of the page container object
        $page = new stdClass();
        //If it is an array, array page is made
        if (is_array($model)) {
            $items = $model;
            $n = count($items);
            //if the start is greater than or equal to the item count,
            //then the page does not exist, except when it is page 1
            if ($page_number > 1 && $start >= $n) {
                throw new KumbiaException("The page $page_number does not exist on the page");
            }
            $page->items = array_slice($items, $start, $per_page);
        } else {
            //Arrangement containing the arguments for the find
            $find_args = array();
            $conditions = null;
            //Assigning Search Parameters
            if (isset($params['conditions'])) {
                $conditions = $params['conditions'];
            } elseif (isset($params[1])) {
                $conditions = $params[1];
            }
            if (isset($params['columns'])) {
                $find_args[] = "columns: {$params['columns']}";
            }
            if (isset($params['join'])) {
                $find_args[] = "join: {$params['join']}";
            }
            if (isset($params['group'])) {
                $find_args[] = "group: {$params['group']}";
            }
            if (isset($params['having'])) {
                $find_args[] = "having: {$params['having']}";
            }
            if (isset($params['order'])) {
                $find_args[] = "order: {$params['order']}";
            }
            if (isset($params['distinct'])) {
                $find_args[] = "distinct: {$params['distinct']}";
            }
            if (isset($conditions)) {
                $find_args[] = $conditions;
            }
            //count the records
            $n = call_user_func_array(array($model, 'count'), $find_args);
            //if the start is greater than or equal to the item count,
            //then the page does not exist, except when it is page 1
            if ($page_number > 1 && $start >= $n) {
                throw new KumbiaException("The page $page_number does not exist on the page");
            }
            //We assign the offset and limit
            $find_args[] = "offset: $start";
            $find_args[] = "limit: $per_page";
            //Search is done
            $page->items = call_user_func_array(array($model, 'find'), $find_args);
        }
        //Page calculations are made
        $page->next = ($start + $per_page) < $n ? ($page_number + 1) : false;
        $page->prev = ($page_number > 1) ? ($page_number - 1) : false;
        $page->current = $page_number;
        $page->total = ceil($n / $per_page);
        $page->count = $n;
        $page->per_page = $per_page;

        return $page;
    }

    /**
     * Pager per sql.
     *
     * @param object $model Model to paginate
     * @param string $sql   Sql query
     *
     * page: page number to display (default page 1)
     * per_page: number of records per page (default 10 records per page)
     *
     *
     * Returns a PageObject that has the following attributes:
     *  next: next page number, if there is no next page then it is false
     *  prev: previous page number, if there is no previous page then it is false
     *  current: current page number
     *  total: total pages that can be displayed
     *  items: array of page records
     *  count: Total records
     *
     * @example
     *  $page = paginate_by_sql('user', 'SELECT * FROM user' , 'per_page: 5', "page: $page_num");
     *
     * @return stdClass
     * */
    public static function paginate_by_sql($model, $sql)
    {
        $params = Util::getParams(func_get_args());
        $page_number = isset($params['page']) ? (int) $params['page'] : 1;
        $per_page = isset($params['per_page']) ? (int) $params['per_page'] : 10;
        //If the page or per page is less than 1 (0 or negative)
        if ($page_number < 1 || $per_page < 1) {
            throw new KumbiaException("The page $page_number does not exist in the pager");
        }
        $start = $per_page * ($page_number - 1);
        //Instance of the page container object
        $page = new stdClass();
        //I count the appearances through a derived table
        $n = $model->count_by_sql("SELECT COUNT(*) FROM ($sql) AS t");
        //if the start is greater than or equal to the item count,
        //then the page does not exist, except when it is page 1
        if ($page_number > 1 && $start >= $n) {
            throw new KumbiaException("The page $page_number does not exist in the pager");
        }
        $page->items = $model->find_all_by_sql($model->limit($sql, "offset: $start", "limit: $per_page"));
        //Page calculations are made
        $page->next = ($start + $per_page) < $n ? ($page_number + 1) : false;
        $page->prev = ($page_number > 1) ? ($page_number - 1) : false;
        $page->current = $page_number;
        $page->total = ceil($n / $per_page);
        $page->count = $n;
        $page->per_page = $per_page;

        return $page;
    }
}
