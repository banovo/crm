<?php

namespace Acme\Bundle\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Customer entity controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/customer")
 */
class CustomerController extends Controller
{

    /**
     * Get product manager
     * @return SimpleEntityManager
     */
    protected function getCustomerManager()
    {
        return $this->container->get('customer_manager');
    }

    /**
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $customers = $this->getCustomerManager()->getEntityRepository()->findByWithAttributes();

        return array('customers' => $customers);
    }

    /**
     * @Route("/querylazyload")
     * @Template("AcmeCustomerBundle:Customer:index.html.twig")
     *
     * @return multitype
     */
    public function querylazyloadAction()
    {
        $customers = $this->getCustomerManager()->getEntityRepository()->findBy(array());

        return array('customers' => $customers);
    }

    /**
     * @Route("/queryonlydob")
     * @Template("AcmeCustomerBundle:Customer:index.html.twig")
     *
     * @return multitype
     */
    public function queryonlydobAction()
    {
        $customers = $this->getCustomerManager()->getEntityRepository()->findByWithAttributes(array('dob'));

        return array('customers' => $customers);
    }

    /**
     * @Route("/queryonlydobandgender")
     * @Template("AcmeCustomerBundle:Customer:index.html.twig")
     *
     * @return multitype
     */
    public function queryonlydobandgenderAction()
    {
        $customers = $this->getCustomerManager()->getEntityRepository()->findByWithAttributes(array('dob', 'gender'));

        return array('customers' => $customers);
    }

    /**
     * @Route("/queryfilterfirstname")
     * @Template("AcmeCustomerBundle:Customer:index.html.twig")
     *
     * @return multitype
     */
    public function queryfilterfirstnameAction()
    {
        $customers = $this->getCustomerManager()
                          ->getEntityRepository()
                          ->findByWithAttributes(
                              array(),
                              array('firstname' => 'Nicolas')
                          );

        return array('customers' => $customers);
    }

    /**
     * @Route("/queryfilterfirstnameandcompany")
     * @Template("AcmeCustomerBundle:Customer:index.html.twig")
     *
     * @return multitype
     */
    public function queryfilterfirstnameandcompanyAction()
    {
        $customers = $this->getCustomerManager()
                          ->getEntityRepository()
                          ->findByWithAttributes(
                              array('company'),
                              array('firstname' => 'Nicolas', 'company' => 'Akeneo')
                          );

        return array('customers' => $customers);
    }

    /**
     * @Route("/queryfilterfirstnameandlimit")
     * @Template("AcmeCustomerBundle:Customer:index.html.twig")
     *
     * @return multitype
     */
    public function queryfilterfirstnameandlimit()
    {
        // initialize vars
        $limit = 10;
        $start = 0;

        // get customers filtered by firstname = "Nicolas" and limited
        $customers = $this->getCustomerManager()
                          ->getEntityRepository()
                          ->findByWithAttributes(
                              array(),
                              array('firstname' => 'Nicolas'),
                              null,
                              $limit,
                              $start
                          );

        return array('customers' => $customers);
    }

    /**
     * @Route("/queryfilterfirstnameandorderbirthdatedesc")
     * @Template("AcmeCustomerBundle:Customer:index.html.twig")
     *
     * @return multitype
     */
    public function queryfilterfirstnameandorderbirthdatedescAction()
    {
        $customers = $this->getCustomerManager()
                          ->getEntityRepository()
                          ->findByWithAttributes(
                              array('dob'),
                              array('firstname' => 'Nicolas'),
                              array('dob' => 'desc')
                          );

        return array('customers' => $customers);
    }

    /**
     * @param integer $id
     *
     * @Route("/view/{id}")
     * @Template()
     *
     * @return multitype
     */
    public function viewAction($id)
    {
        // with lazy loading
        // $customer = $this->getCustomerManager()->getEntityRepository()->find($id);

        // with any values
        $customer = $this->getCustomerManager()->getEntityRepository()->findWithAttributes($id);

        return array('customer' => $customer);
    }

    /**
     * @Route("/insert")
     *
     * @return multitype
     */
    public function insertAction()
    {
        $messages = array();

        // get attributes
        $attCompany = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode('company');
        $attDob = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode('dob');
        $attGender = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode('gender');
        // get first attribute option
        $optGender = $this->getCustomerManager()->getAttributeOptionRepository()->findOneBy(array('attribute' => $attGender));

        for ($ind= 1; $ind < 100; $ind++) {

            // add customer with email, firstname, lastname, dob
            $custEmail = 'email-'.($ind++).'@mail.com';
            $customer = $this->getCustomerManager()->getEntityRepository()->findOneByEmail($custEmail);
            if ($customer) {
                $messages[]= "Customer ".$custEmail." already exists";
            } else {
                $customer = $this->getCustomerManager()->createEntity();
                $customer->setEmail($custEmail);
                $customer->setFirstname($this->generateFirstname());
                $customer->setLastname($this->generateLastname());
                // add dob value
                if ($attCompany) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attDob);
                    $value->setData(new \DateTime($this->generateBirthDate()));
                    $customer->addValue($value);
                }
                $messages[]= "Customer ".$custEmail." has been created";
                $this->getCustomerManager()->getStorageManager()->persist($customer);
            }

            // add customer with email, firstname, lastname, company and gender
            $custEmail = 'email-'.($ind++).'@mail.com';
            $customer = $this->getCustomerManager()->getEntityRepository()->findOneByEmail($custEmail);
            if ($customer) {
                $messages[]= "Customer ".$custEmail." already exists";
            } else {
                $customer = $this->getCustomerManager()->createEntity();
                $customer->setEmail($custEmail);
                $customer->setFirstname($this->generateFirstname());
                $customer->setLastname($this->generateLastname());
                // add company value
                if ($attCompany) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attCompany);
                    $value->setData('Akeneo');
                    $customer->addValue($value);
                }
                // add date of birth
                if ($attDob) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attDob);
                    $value->setData(new \DateTime($this->generateBirthDate()));
                    $customer->addValue($value);
                }
                // add gender
                if ($attGender) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attGender);
                    $value->setData($optGender);  // we set option as data, you can use $value->setOption($optGender) too
                    $customer->addValue($value);
                }
                $messages[]= "Customer ".$custEmail." has been created";
                $this->getCustomerManager()->getStorageManager()->persist($customer);
            }
        }

        $this->getCustomerManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('acme_customer_customer_index'));
    }

    /**
     * Generate firstname
     * @return string
     */
    protected function generateFirstname()
    {
        $listFirstname = array('Nicolas', 'Romain');
        $random = rand(0, count($listFirstname)-1);

        return $listFirstname[$random];
    }

    /**
     * Generate lastname
     * @return string
     */
    protected function generateLastname()
    {
        $listLastname = array('Dupont', 'Monceau');
        $random = rand(0, count($listLastname)-1);

        return $listLastname[$random];
    }

    /**
     * Generate birthdate
     * @return string
     */
    protected function generateBirthDate()
    {
        $year  = rand(1980, 2000);
        $month = rand(1, 12);
        $day   = rand(1, 28);

        return $year .'-'. $month .'-'. $day;
    }

}
