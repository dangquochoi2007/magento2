<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Customer\Test\Fixture\Address;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for Delete TaxRuleEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Tax Rule is created
 *
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Sales > Tax Rules
 * 3. Select required tax rule from preconditions
 * 4. Click on the "Delete Rule" button
 * 5. Perform all assertions
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-20924
 */
class DeleteTaxRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Tax Rule grid page
     *
     * @var TaxRuleIndex
     */
    protected $taxRuleIndexPage;

    /**
     * Tax Rule new and edit page
     *
     * @var TaxRuleNew
     */
    protected $taxRuleNewPage;

    /**
     * Preparing data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $customer = $fixtureFactory->createByCode('customer', ['dataSet' => 'default']);
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param TaxRuleIndex $taxRuleIndexPage
     * @param TaxRuleNew $taxRuleNewPage
     */
    public function __inject(
        TaxRuleIndex $taxRuleIndexPage,
        TaxRuleNew $taxRuleNewPage
    ) {
        $this->taxRuleIndexPage = $taxRuleIndexPage;
        $this->taxRuleNewPage = $taxRuleNewPage;
    }

    /**
     * Delete Tax Rule Entity test
     *
     * @param TaxRule $taxRule
     * @param Address $address
     * @param array $shipping
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testDeleteTaxRule(
        TaxRule $taxRule,
        Address $address,
        array $shipping
    ) {
        // Precondition
        $taxRule->persist();

        // Steps
        $filters = [
            'code' => $taxRule->getCode(),
        ];
        $this->taxRuleIndexPage->open();
        $this->taxRuleIndexPage->getTaxRuleGrid()->searchAndOpen($filters);
        $this->taxRuleNewPage->getFormPageActions()->delete();
    }
}
