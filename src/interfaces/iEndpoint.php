<?php
namespace src\interfaces;

interface IEndpoint {

    public function set($data);
    public function get($data);
    public function update($data);
    public function delete($data);
    
}