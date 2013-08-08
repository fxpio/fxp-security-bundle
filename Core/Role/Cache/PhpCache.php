<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Core\Role\Cache;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * PHP File Cache for role hierarchy.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PhpCache implements CacheInterface
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor.
     *
     * @param string     $cacheDir
     * @param Filesystem $filesystem
     */
    public function __construct($cacheDir, Filesystem $filesystem)
    {
        $this->cacheDir = $cacheDir;
        $this->filesystem = $filesystem;

    }

    /**
     * Writes cache.
     *
     * @param string $id      The unique name
     * @param string $content The content to write in the cache
     *
     * @throws \RuntimeException When cache file can't be wrote
     */
    public function write($id, array $roles)
    {
        $content = "<?php\n\nreturn array(\n";

        foreach ($roles as $role) {
            if ($role instanceof RoleInterface) {
                $classname = get_class($role);
                $role = $role->getRole();
                $role = "new \\$classname('$role')";

                $content .= "    $role,\n";
            }
        }

        $content .= ");\n";

        $mode = 0666 & ~umask();
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->cacheDir.'/'.$id.'.php', $content, $mode);
    }

    /**
     * Reads cache.
     *
     * @param string $id The unique name
     *
     * @return array|null The roles or null if file does not exist
     */
    public function read($id)
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($this->cacheDir.'/'.$id.'.php')) {
            return include $this->cacheDir.'/'.$id.'.php';
        }

        return null;
    }

    /**
     * Invalidates cache.
     */
    public function flush()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->cacheDir);
        $filesystem->mkdir($this->cacheDir, 0777);
    }
}
