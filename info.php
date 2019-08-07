
<?php
$json = '                {
                "productOption": {
                    "extensionAttributes": {
                        "configurableItemOptions": [
                          {
                            "optionId": "178",
                            "optionValue": 45,
                            "extensionAttributes": {}
                          }
                        ]
                    }
                }
                }    ';
$result = json_decode ($json);
echo "<pre>";print_r($result);die;
?>

