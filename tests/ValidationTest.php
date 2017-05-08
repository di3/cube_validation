<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ValidationTestCase extends Validation {
  protected function validateEquals($data, $value) {
    return $data === $value;
  }
  protected function getValueValue($value) {
    return $value . '_x';
  }
}

final class ValidationTest extends TestCase {

  public function providerValid() {
    return [
      "test1: no validation" => [[['test1'=>'value1','value'=>'test'],[],null]],
      "test2: valid validation" => [[['test2'=>'value2','value'=>'test'],['test2'],null]],
      "test3: valid validation" => [[['test3'=>'value3','value'=>'test'],['test3'=>["required", "equals" => "value3"]],null]]
    ];
  }

  public function providerInvalid() {
    return [
      "test1: invalid validation" => [[['test1'=>'value1'],['testfail'],null]],
      "test2: invalid validation" => [[['test2'=>'value2'],['test2'=>["required", "equals" => "valuefail"]],null]]
    ];
  }

  /**
   * @dataProvider providerValid
   */
  public function testConstructor($data) {
    $Validation = new Validation($data[0],$data[1],$data[2]);
    $this->assertInstanceOf(Validation::class, $Validation);
    return $Validation;
  }

  /**
   * unkown validations should pass
   */
  public function testUnknownInvalidValidation() {
    $Validation = new Validation(['test1'=>'value1'],['test1'=>["required", "equals" => "valuefail"]],null);
    $this->assertInstanceOf(Validation::class, $Validation);
    $this->assertEquals(0, $Validation->parseAll());
    $this->assertTrue($Validation->isValid());
  }
  
  /**
   * @dataProvider providerValid
   */
  public function testValidation($data) {
    $Validation = new ValidationTestCase($data[0],$data[1],$data[2]);
    $this->assertInstanceOf(Validation::class, $Validation);
    $this->assertEquals(0, $Validation->parseAll());
    $this->assertTrue($Validation->isValid());
    $this->assertEquals('test_x', $Validation->getValue('value'));
  }

  /**
   * @dataProvider providerInvalid
   */
  public function testInvalidValidation($data) {
    $Validation = new ValidationTestCase($data[0],$data[1],$data[2]);
    $this->assertInstanceOf(Validation::class, $Validation);
    $this->assertEquals(1, $Validation->parseAll());
    $this->assertFalse($Validation->isValid());
  }

  public function testErrorHandlerDefaultMessage() {
    $Validation = new ValidationTestCase(['test2'=>'value2'],['test2'=>["required", "equals" => "valuefail"]],null);
    $ValidationErrorHandler = $Validation->getErrorHandler();
    $this->assertInstanceOf(Validation::class, $Validation);
    $this->assertInstanceOf(ValidationError::class, $ValidationErrorHandler);
    $ValidationErrorHandler->addMessage("*","*","invalid");
    $this->assertEquals(1, $Validation->parseAll());
    $this->assertEquals(["equals"=>"invalid"], $Validation->getError("test2"));
  }

  public function testErrorHandlerMessage() {
    $Validation = new ValidationTestCase(['test2'=>'value2'],['test2'=>["required", "equals" => "valuefail"]],null);
    $ValidationErrorHandler = $Validation->getErrorHandler();
    $this->assertInstanceOf(Validation::class, $Validation);
    $this->assertInstanceOf(ValidationError::class, $ValidationErrorHandler);
    $ValidationErrorHandler->addMessage("*","*","invalid");
    $ValidationErrorHandler->addMessage("*","equals","invalid equals");
    $this->assertEquals(1, $Validation->parseAll());
    $this->assertEquals(["equals"=>"invalid equals"], $Validation->getError("test2"));
  }
}
