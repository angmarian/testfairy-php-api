<?php

namespace TestFairy\Tests;

use TestFairy\TestFairyBasicAuthClient;

class TestFairyApiTest extends \PHPUnit_Framework_TestCase
{

    protected $config = array(
        'email'   => 'gil@megidish.net',
        'api_key' => '9dc08e8d93efd8622178f0c61faeaf112fbafcb4',
    );

    public function testFactory()
    {

        $testfairy = TestFairyBasicAuthClient::factory($this->config);
        $this->assertInstanceOf("TestFairy\TestFairyBasicAuthClient", $testfairy);

    }

    public function testConstructor()
    {

        $testfairy = new TestFairyBasicAuthClient($this->config);
        $this->assertInstanceOf("TestFairy\TestFairyBasicAuthClient", $testfairy);

    }

    public function testAuthIsSet()
    {
        $testfairy = TestFairyBasicAuthClient::factory($this->config);
        $auth = $testfairy->getDefaultOption('auth');

        $this->assertEquals(2, count($auth));
        $this->assertEquals($this->config['email'], $auth[0]);
        $this->assertEquals($this->config['api_key'], $auth[1]);
    }

    public function testGetServiceDescriptionFromFile()
    {
        $testfairy = new TestFairyBasicAuthClient($this->config);

        $sd = $testfairy->getServiceDescriptionFromFile(__DIR__ . '/../src/TestFairy/Service/config/testfairy.json');
        $this->assertInstanceOf('Guzzle\Service\Description\ServiceDescription', $sd);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetServiceDescriptionFromFileNoFile()
    {
        $testfairy = new TestFairyBasicAuthClient($this->config);
        $testfairy->getServiceDescriptionFromFile('');
    }

    /**
     * @expectedException \Guzzle\Common\Exception\InvalidArgumentException
     */
    public function testFactoryEmptyArgs()
    {
        TestFairyBasicAuthClient::factory();
    }

    /**
     * @expectedException \Guzzle\Common\Exception\InvalidArgumentException
     */
    public function testFactoryMissingArgs()
    {
        TestFairyBasicAuthClient::factory(array('email' => 'gil@megidish.net'));
    }

    public function testMethods()
    {

        $testfairy = TestFairyBasicAuthClient::factory($this->config);

        // Testing "getProjects", "getBuilds" & "getSessions"

        $projects = $testfairy->getProjects();

        $this->assertEquals($projects->status, 'ok');
        $this->assertNotEmpty($projects->projects);
        $this->assertInternalType('array', $projects->projects);

        do {
            $randomProject = $projects->projects[rand(0, count($projects->projects) - 1)];

            $this->assertInternalType('object', $randomProject);
            $this->assertNotEmpty($randomProject->id);

            $builds = $testfairy->getBuilds(array('project_id' => $randomProject->id));

            if (!empty($builds->builds)) {
                $this->assertEquals($builds->status, 'ok');
                $this->assertInternalType('array', $builds->builds);

                $randomBuild = $builds->builds[rand(0, count($builds->builds) - 1)];

                $this->assertInternalType('object', $randomBuild);
                $this->assertNotEmpty($randomBuild->id);

                $sessions = $testfairy->getSessions(array('project_id' => $randomProject->id, 'build_id' => $randomBuild->id));
            }
        } while (empty($sessions->sessions));

        $this->assertEquals($sessions->status, 'ok');
        $this->assertNotEmpty($sessions->sessions);
        $this->assertInternalType('array', $sessions->sessions);

        $randomSession = $sessions->sessions[rand(0, count($sessions->sessions) - 1)];

        $this->assertInternalType('object', $randomSession);
        $this->assertNotEmpty($randomSession->id);

        // Testing "getSession"

        $session = $testfairy->getSession(array(
                        'project_id' => $randomProject->id,
                        'build_id'   => $randomBuild->id,
                        'session_id' => $randomSession->id
                    ));

        $this->assertEquals($session->status, 'ok');
        $this->assertNotEmpty($session->session->ipAddress);

        $ipAddress = $session->session->ipAddress;

        // Testing "search"

        $search = $testfairy->search(array(
                    'ip' => $ipAddress,
                    'page' => 1,
                    'perPage' => 3,
                    'projectId' => $randomProject->id,
                    'buildId' => $randomBuild->id,
                  ));

        $this->assertEquals($search->status, 'ok');
        $this->assertNotEmpty($search->sessions);
        $this->assertLessThan(4, count($search->sessions));

        // Testing "getCrashes"

        $crashes = $testfairy->getCrashes(array('project_id' => $randomProject->id, 'build_id' => $randomBuild->id));

        $this->assertEquals($crashes->status, 'ok');
        $this->assertInternalType('array', $crashes->crashes);

        // Testing "getBuildTesters"

        $buildTesters = $testfairy->getBuildTesters(array('project_id' => $randomProject->id, 'build_id' => $randomBuild->id));

        $this->assertEquals($buildTesters->status, 'ok');
        $this->assertInternalType('array', $buildTesters->testers);

        // Testing "getTesters" & "addTester"

        $testers = $testfairy->getTesters();

        $this->assertEquals($testers->status, 'ok');
        $this->assertInternalType('array', $testers->testers);

        // Testing "addTesters"

        $newEmail = 'api-test-' . time() . '@example.com';

        $newTester = $testfairy->addTester(array(
            'email' => $newEmail,
        ));

        $lookForTester = null;

        $newTesters = $testfairy->getTesters()->testers;

        foreach ($newTesters as $item) {
            if ($item->email === $newEmail) {
                $lookForTester = $item;
            }
        }

        $this->assertEquals($lookForTester->email, $newEmail);

    }
}
