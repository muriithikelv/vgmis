<?php


function html($text)
{
  return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
}

function htmlout($text)
{
  echo html($text);
}







?>