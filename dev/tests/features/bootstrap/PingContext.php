<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Curl\Curl;
use Assert\Assertion;
use Assert\Assert;

/**
 * Defines application features from the specific context.
 */
class PingContext implements Context
{
    protected int $port;

    protected Curl $curl;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given The service port is defined
     */
    public function theServicePortIsDefined()
    {
        $this->port = getenv('HTTP_PORT');
    }

    /**
     * @When I do http get
     */
    public function iDoHttpGet()
    {
        $this->curl = new Curl();
        $this->curl->get('http://localhost:'.$this->port);
    }

    /**
     * @Then I should get an ack response
     */
    public function iShouldGetAnAckResponse()
    {
        Assert::that($this->curl->error)->noContent();
        Assert::that($this->curl->response)->notEmpty();
        Assert::that('Content-Type: application/json')->inArray($this->curl->response_headers);
        Assert::that($this->curl->response)->isJsonString();
        Assert::that(json_decode($this->curl->response))->propertyExists('ack');
        Assert::that(json_decode($this->curl->response)->ack)->notEmpty();
    }
}
