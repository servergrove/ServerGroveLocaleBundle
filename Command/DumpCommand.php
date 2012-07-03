<?php

namespace ServerGrove\LocaleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Assetic\AssetWriter;

/**
 * Class DumpCommand
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class DumpCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('locale:assets:install')
            ->setDescription('Installs the locale assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $kernel \Symfony\Component\HttpKernel\Kernel */
        $kernel = $this->getContainer()->get('kernel');

        /** @var $factory \ServerGrove\LocaleBundle\Asset\Factory\AssetFactory */
        $factory = $this->getContainer()->get('servergrove_locale.asset_factory');

        /** @var $writer AssetWriter */
        $writer = new AssetWriter(dirname($kernel->getRootDir()).'/web');
        $writer->writeManagerAssets($factory->getAssetManager());
    }
}
