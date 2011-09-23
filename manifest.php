<?PHP

// manifest file for information regarding application of new code
$manifest = array(
    // only install on the following regex sugar versions (if empty, no check)
    'acceptable_sugar_flavors' => array (
        0 => 'CE',
    ),
    'acceptable_sugar_versions' => array (
        'regex_matches' => array (
            0 => "6\\.*"
        ),
    ),

    // name of new code
    'name' => 'iCal Patch',

    // description of new code
    'description' => 'A patch to add iCal support to SugarCRM 6.2 or greater',

    // author of new code
    'author' => 'owenc@hubris.net, rgauss@drscomptech.com',

    // date published
    'published_date' => '20110715',

    // version of code
    'version' => '0.8.6',

    // type of code (valid choices are: full, langpack, module, patch, theme )
    'type' => 'module',

    // icon for displaying in UI (path to graphic contained within zip package)
    'icon' => '',

    'is_uninstallable' => true
);

$installdefs = array(
        'copy' => array(
                        array('from'=> '<basepath>/ical_server.php',
                              'to'=> 'ical_server.php',
                              ),
                        array('from'=> '<basepath>/custom',
                              'to'=> 'custom',
                              ),
                    ),
);

?>
