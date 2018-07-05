<?php
/**
 * Created by PhpStorm.
 * User: bennet
 * Date: 06.07.18
 * Time: 00:43
 */

namespace intraframe\ViewModels;


use intraframe\Router\ViewModel;

class NotFoundViewModel extends ViewModel {


    private $model = [];

    function __construct() {
        parent::__construct('not-found');
    }

    function getModel() {
        return $this->model;
    }

    function run($params) {
        http_response_code(404);
    }
}