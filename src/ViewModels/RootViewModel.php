<?php
/**
 * Created by PhpStorm.
 * User: bennet
 * Date: 06.07.18
 * Time: 00:42
 */

namespace intraframe\ViewModels;


use intraframe\Router\ViewModel;

class RootViewModel extends ViewModel {

    private $model = [];

    function __construct() {
        parent::__construct('main');
    }

    public function getModel() {
        return $this->model;
    }
    public function run($params) {
        $this->model['title'] = "test";
    }
}