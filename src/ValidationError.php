<?php
class ValidationError extends Exception implements Iterator, Countable {
  private $messages = [];
  private $defaultMessages = [];
  private $errors = [];

  //iterator
  public function rewind() {
    reset($this->errors);
  }
  public function current() {
    return current($this->errors);
  }
  public function key() {
    return key($this->errors);
  }
  public function next() {
    return next($this->errors);
  }
  public function valid() {
    return $this->current() !== false;
  }

  //Countable
  public function count() {
    return count($this->errors);
  }

  //toJson
  public function __toString() {
    return json_encode($this->errors, JSON_UNESCAPED_UNICODE);
  }

  public function __construct() {
  }
  public function addMessage($name, $ruleName, $message) {
    if (!isset($this->messages[$name])) $this->messages[$name] = [];
    $this->messages[$name][$ruleName] = $message;
  }
  public function addError($name, $ruleName, $ruleValue) {
    if (!isset($this->errors[$name])) $this->errors[$name] = [];
    if (is_numeric($ruleName)) {
      $ruleName = $ruleValue;
    }
    if (isset($this->messages[$name]) && isset($this->messages[$name][$ruleName])) {
      $message = $this->messages[$name][$ruleName];
    } elseif (isset($this->messages["*"])) {
      if (isset($this->messages["*"][$ruleName])) $message = $this->messages["*"][$ruleName];
      elseif (isset($this->messages["*"]["*"])) $message = $this->messages["*"]["*"];
      else $message = false;
    } else {
      $message = false;
    }

    if ($message !== false) $this->errors[$name][$ruleName] = $message;

  }
  public function getError($name = null, $ruleName = null) {
    if ($name === null && $ruleName === null) return $this->errors;
    elseif ($name === null) {
      $return = [];
      foreach ($this->errors as $k => $v) {
        if (isset($v[$ruleName])) {
          $return[$k] = [$ruleName => $v[$ruleName]];
        }
      }
      return $return;
    } elseif ($ruleName !== null) return (isset($this->errors[$name]) && isset($this->errors[$name][$ruleName])) ? $this->errors[$name][$ruleName] : null;
    elseif (isset($this->errors[$name])) return $this->errors[$name];
    else return [];
  }
}

