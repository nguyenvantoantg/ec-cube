<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Util;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\AddressType;
use Eccube\Form\Type\KanaType;
use Eccube\Form\Type\NameType;
use Eccube\Form\Type\PhoneNumberType;
use Eccube\Form\Type\PostalType;
use Eccube\Form\Type\RepeatedEmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FormUtil
{
    public static function buildCustomerInputForm(FormBuilderInterface &$builder, EccubeConfig $config, $locale = 'ja')
    {
        $builder
            ->add('name', NameType::class, [
                'required' => true,
            ])
            ->add('company_name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => $config->get('eccube_stext_len'),
                    ]),
                ],
            ])
            ->add('address', AddressType::class, [
                'required' => true,
            ])
            ->add('phone_number', PhoneNumberType::class, [
                'required' => true,
            ])
            ->add('email', RepeatedEmailType::class);

        switch ($locale) {
            case 'ja':
                $builder->add('kana', KanaType::class, [
                    'required' => true,
                ])->add('postal_code', PostalType::class, [
                    'required' => true,
                ]);
                break;
        }
    }

    /**
     * formオブジェクトからviewDataを取得する.
     *
     * @param FormInterface $form
     *
     * @return array
     */
    public static function getViewData(FormInterface $form)
    {
        $viewData = [];
        $forms = $form->all();

        if (empty($forms)) {
            return $form->getViewData();
        }

        foreach ($forms as $key => $value) {
            // choice typeは各選択肢もFormとして扱われるため再帰しない.
            if ($value->getConfig()->hasOption('choices')) {
                $viewData[$key] = $value->getViewData();
            } else {
                $viewData[$key] = self::getViewData($value);
            }
        }

        return $viewData;
    }

    /**
     * formオブジェクトにviewdataをsubmitし, マッピングした結果を返す.
     *
     * @param FormInterface $form
     * @param $viewData
     *
     * @return mixed
     */
    public static function submitAndGetData(FormInterface $form, $viewData)
    {
        $form->submit($viewData);

        return $form->getData();
    }
}
