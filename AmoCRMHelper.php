<?php

class AmoCRMHelper
{
    static public function getComplexData($name, $email, $phone, $price, $leadName = 'Тестова сделка'): array
    {
        return [
            [
                'name' => $leadName,
                'price' => (float)$price,
                '_embedded' => [
                    'contacts' => [
                        [
                            'name' => $name,
                            'custom_fields_values' => [
                                [
                                    'field_id' => 1287507,
                                    'values' => [
                                        [
                                            'value' => $email
                                        ]
                                    ]
                                ],
                                [
                                    'field_id' => 1287505,
                                    'values' => [
                                        [
                                            'value' => $phone
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}