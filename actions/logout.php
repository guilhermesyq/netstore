<?php
  session_start();
  $_SESSION = [];
  session_destroy();
  header("Location: /netstore/home");
  exit;
