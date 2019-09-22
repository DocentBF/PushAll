<?php
include_once MODX_CORE_PATH . 'model/modx/transport/modpackagebuilder.class.php';
class encPackageBuilder extends modPackageBuilder {
    /**
     * @param string $name
     * @param string $version
     * @param string $release
     * @return xPDOTransport
     */
    public function createPackage($name, $version, $release = '') {
        parent::createPackage($name, $version, $release);
        $this->package->put(new xPDOFileVehicle, [
            'vehicle_class' => 'xPDOFileVehicle',
            'object' => [
                'source' => dirname(__FILE__) . '/encryptedvehicle.class.php',
                //'target' => "return MODX_CORE_PATH . 'components/" . PKG_NAME_LOWER . "/';"
                'target' => "return MODX_CORE_PATH . 'components/modstoreencrypt/';"
            ]
        ]);

        $this->package->put(new xPDOScriptVehicle, [
            'vehicle_class' => 'xPDOScriptVehicle',
            'object' => [
                'source' => dirname(dirname(dirname(__FILE__))) . '/resolvers/resolve.encryption.php'
            ]
        ]);
        return $this->package;
    }
    /**
     * Puts the vehicle into the package.
     *
     * @access public
     * @param modTransportVehicle $vehicle The vehicle to insert into the package.
     * @return boolean True if successful.
     */
    public function putVehicle($vehicle) {
        $attr = $vehicle->compile();
        $obj = $vehicle->fetch();
        $attr = array_merge($attr, array(
            'vehicle_class' => EncryptedVehicle::class,
        ));
        return $this->package->put($obj, array_merge($attr));
    }

    public function pack() {
        $this->package->put(new xPDOScriptVehicle, [
            'vehicle_class' => 'xPDOScriptVehicle',
            'object' => [
                'source' => dirname(dirname(dirname(__FILE__))) . '/resolvers/resolve.encryption.php'
            ]
        ]);
        return parent::pack();
    }
}