<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprestriction\Block\Adminhtml\Rule\Edit\Tab;


class Apply extends AbstractTab
{
    protected function getLabel()
    {
        return __('Coupons');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fldInfo = $form->addFieldset('apply_restriction', ['legend' => __('Apply Restrictions Only With')]);

        $promoShippingRulesUrl = $this->getUrl('sales_rule/promo_quote');

        $fldInfo->addField('coupon', 'text', array(
            'label'     => __('Coupon Code'),
            'name'      => 'coupon',
            'note'      => __('Apply this restriction with coupon only. You should configure coupon in <a href="%1"><span>Promotions / Shopping Cart Rules</span></a> area first.', $promoShippingRulesUrl),
        ));

        $fldInfo->addField('discount_id', 'select', array(
            'label'     => __('Shopping Cart Rule (discount)'),
            'name'      => 'discount_id',
            'values'    => $this->helper->getAllRules(),
            'note'      => __('Apply this restriction with ANY coupon from specified discount rule. You should configure the rule in <a href="%1"><span>Promotions / Shopping Cart Price Rules</span></a> area first. Useful when you have MULTIPLE coupons in one rule.', $promoShippingRulesUrl),
        ));

        $fldInfo = $form->addFieldset('not_apply_restriction', array('legend'=> __('Do NOT Apply Restrictions With')));

        $fldInfo->addField('coupon_disable', 'text', array(
            'label'     => __('Coupon code'),
            'name'      => 'coupon_disable',
            'note'      => __('Not apply this restriction with coupon. You should configure coupon in <a href="%1"><span>Promotions / Shopping Cart Rules</span></a> area first.', $promoShippingRulesUrl),
        ));

        $fldInfo->addField('discount_id_disable', 'select', array(
            'label'     => __('Shopping Cart Rule (discount)'),
            'name'      => 'discount_id_disable',
            'values'    => $this->helper->getAllRules(),
            'note'      => __('Not apply this restriction with ANY coupon from specified discount rule. You should configure the rule in <a href="%1"><span>Promotions / Shopping Cart Price Rules</span></a> area first. Useful when you have MULTIPLE coupons in one rule.', $promoShippingRulesUrl),
        ));

        $form->setValues($model->getData());
        $form->addValues(['id'=>$model->getId()]);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
