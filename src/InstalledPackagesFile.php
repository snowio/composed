<?php
namespace Composed;

/**
 * @author Josh Di Fabio <joshdifabio@gmail.com>
 */
class InstalledPackagesFile
{
    private $root;
    private $json;
    private $packages;

    public function __construct(RootPackage $root, JsonObject $json)
    {
        $this->root = $root;
        $this->json = $json;
    }

    public static function fromFilePath(RootPackage $root, string $filePath) : self
    {
        return new self($root, JsonObject::fromFilePath($filePath));
    }

    public function getPackages() : PackageCollection
    {
        if (null === $this->packages) {
            $packages = [];
            $packagesData = $this->json->get();
            // Composer 2 wraps the package list as {"packages": [...], "dev": ..., ...}
            // instead of Composer 1's bare array [{...}, {...}, ...]. Support both.
            if (is_array($packagesData) && array_key_exists('packages', $packagesData)) {
                $packagesData = $packagesData['packages'];
            }
            foreach ($packagesData as $packageData) {
                $package = Package::fromArray($this->root, $packageData);
                $packages[$package->getName()] = $package;
            }
            $this->packages = new PackageCollection($packages);
        }

        return $this->packages;
    }

    public function get($keys = [], $default = null)
    {
        return $this->json->get($keys, $default);
    }
}
