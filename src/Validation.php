<?php
/* Validation 
 * @AUTHOR di3@gmx.net
 */
class Validation {
  protected $data = [];
  protected $validation = [];

  private $isValid = null;
  private $ErrorHandler;

  public function __construct(array $data, array $validation, $ErrorHandler = null) {
    $this->data = $data;
    $this->validation = $validation;
    if ($ErrorHandler === null) {
      $this->ErrorHandler = new ValidationError();
    } else {
      $this->ErrorHandler = $ErrorHandler;
    }
  }

  private function addError($name, $ruleName, $ruleValue) {
    $this->ErrorHandler->addError($name, $ruleName, $ruleValue);
  }

  /**
   * execute the validation
   **/
  public function parse($name, $ruleName, $ruleValue = null): bool {
    $call = 'validate'.ucfirst($ruleName);
    if (method_exists($this, $call)) {
      $dataValue = (isset($this->data[$name])) ? $this->data[$name] : null;
      if (!$this->{$call}($dataValue, $ruleValue)) {
        $this->addError($name, $ruleName, $ruleValue);
        $this->isValid = false;
        return false;
      }
    } else {
      if ($ruleName == 'required') {
        if (empty($this->data[$name])) {
          $this->addError($name, $ruleName, $ruleValue);
          $this->isValid = false;
          return false;
        }
      }
    }
    if ($this->isValid === null) $this->isValid = true;
    return true;
  }

  public function parseCombined($names, $ruleName, $ruleValue = null): bool {
    $ok = true;
    $call = 'validate'.ucfirst($ruleName);
    if (method_exists($this, $call)) {
      $dataValues = [];
      foreach ($names as $name) {
        $dataValues[] = (isset($this->data[$name])) ? $this->data[$name] : null;
      }
      if (!$this->{$call}($dataValues, $ruleValue)) {
        foreach ($names as $name) {
          $this->addError($name, $ruleName, $ruleValue);
        }
        $ok = false;
      }
    } else {
      if ($ruleName == 'required') {
        foreach ($names as $name) {
          if (empty($this->data[$name])) {
            $this->addError($name, $ruleName, $ruleValue);
            $ok = false;
          }
        }
      }
    }
    if ($this->isValid !== false || $this->isValid === null) $this->isValid = $ok;
    return $ok;
  }

  public function parseAll(): int {
    $errors = 0;
    if (count($this->validation) == 0) {
      $this->isValid = true;
      return 0;
    }
    foreach ($this->validation as $name => $value) {
      if (is_numeric($name)) {
        if (is_array($value)) {
          //combined validation
          if (count($value) > 1) {
            foreach ($value as $k => $v) {
              if ($k === 0) continue;
              if (is_numeric($k)) {
                if (!$this->parseCombined($value[0],$v)) $errors++;
              } else {
                if (!$this->parseCombined($value[0],$k,$v)) $errors++;
              }
            }
          } else {
            if (!$this->parseCombined($value[0],'required')) $errors++;
          }
        }
        //name is numeric only have a value -> require rule
        elseif (!$this->parse($value, 'required')) {
          $errors++;
        }
      } else {
        if (!is_array($value)) {
          //value is no array -> just one rule
          if (!$this->parse($name, $value)) {
            $errors++;
          }
        } else {
          //we have a array lets iterate the rules
          foreach ($value as $valueRuleKey => $valueRuleValue) {
            if (is_numeric($valueRuleKey)) {
              if (!$this->parse($name, $valueRuleValue)) {
                $errors++;
              }
            } else {
              if (!$this->parse($name, $valueRuleKey, $valueRuleValue)) {
                $errors++;
              }
            }
          }
        }
      }
    }
    return $errors;
  }

  public function isValid(): bool {
    if ($this->isValid === null) {
       $this->parseAll();
    }
    return $this->isValid;
  }
  public function getValue($name, $nested = null) {
    if ($nested) {
      $data = (isset($this->data[$name]) && isset($this->data[$name][$nested])) ? $this->data[$name][$nested] : null;
    } else {
      $data = (isset($this->data[$name])) ? $this->data[$name] : null;
    }
    $call = 'getValue'.ucfirst($name);
    if (method_exists($this, $call)) {
      return $this->{$call}($data);
    } else {
      return $data;
    }
  }
  public function getError($name = null) {
    return $this->ErrorHandler->getError($name);
  }

  public function getErrorHandler() {
    return $this->ErrorHandler;
  }
}
