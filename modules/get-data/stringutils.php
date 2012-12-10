<?php

  //tests if a given string $haystack begins with the string $needle
  function startsWith($haystack, $needle)
  {
      $length = strlen($needle);
      return (substr($haystack, 0, $length) === $needle);
  }

  //tests if a given string $haystack ends with the string $needle
  function endsWith($haystack, $needle)
  {
      $length = strlen($needle);
      $start  = $length * -1; //negative
      return (substr($haystack, $start) === $needle);
  }
?>