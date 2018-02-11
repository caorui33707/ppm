<?php

function getRedisNew(){
    $redis = new Redis();
    $redis->connect(C("REDIS_HOST"),C("REDIS_PORT"));
    $redis->auth(C("REDIS_AUTH"));
    return $redis;
}