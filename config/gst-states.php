<?php

/**
 * GST state codes (first two digits of a GSTIN) mapped to their state / UT name.
 * A tax invoice has to carry the place of supply by name, not just the code.
 */
return [

    'states' => [
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '25' => 'Daman and Diu',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra',
        '28' => 'Andhra Pradesh (Old)',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
        '96' => 'Foreign Country',
        '97' => 'Other Territory',
        '99' => 'Centre Jurisdiction',
    ],

    /*
     * Codes that still have to resolve when printing an old document, but must not
     * be offered on a new one:
     *   25 - Daman and Diu, merged into 26 on 26 January 2020.
     *   28 - Andhra Pradesh before the split; it now files under 37.
     *   96 - Foreign Country, not a place of supply on a domestic tax invoice.
     */
    'deprecated' => ['25', '28', '96'],

];
