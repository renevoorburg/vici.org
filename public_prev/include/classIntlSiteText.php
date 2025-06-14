<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 28-10-14
 * Time: 18:21
 */

require_once (dirname(__FILE__).'/classDBConnector.php');

class IntlSiteText
{
    private $textsArr = array();

    /**
     * @param DBConnector $db
     * @param $id
     */
    public function __construct(DBConnector $db, $id) {
        $sql = "select ptxt_lang, ptxt_full from ptexts where ptxt_pnt_id=".(int)$id;
        $result = $db->query($sql);

        while ($obj = $result->fetch_object()) {
            $this->textsArr[$obj->ptxt_lang] = $obj->ptxt_full;
        }
        $result->close();
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getText($lang) {
        if (isset($this->textsArr[$lang])) {
            return $this->textsArr[$lang];
        } else {
            return '';
        }
    }

}