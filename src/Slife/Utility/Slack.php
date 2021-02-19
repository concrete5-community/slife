<?php

namespace Slife\Utility;

class Slack
{
    public function makeLink($link, $caption)
    {
        return '<'.$link.'|'.$caption.'>';
    }
}