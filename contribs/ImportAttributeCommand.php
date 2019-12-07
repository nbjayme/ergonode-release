<?php
/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Modified by Nathaniel Jayme
 * Import atrributes and auto-create attribute group if non-existent
 * TODO: Improve attribute group creation to not need reloading of groups 
 *       from database
 *       Be able to work with image and specify  formats or type of image file
 *       Be able to work with select and multi-select 
 */
declare(strict_types = 1);

namespace Ergonode\Attribute\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use Ergonode\Core\Domain\ValueObject\TranslatableString;
use Ergonode\Core\Domain\ValueObject\Language;

use Ergonode\Attribute\Domain\Query\AttributeGroupQueryInterface;
use Ergonode\Attribute\Domain\Query\AttributeQueryInterface;
use Ergonode\Attribute\Domain\Entity\Attribute;
use Ergonode\Attribute\Domain\Command\CreatAttributeCommand;
use Ergonode\Attribute\Domain\Command\Group\CreateAttributeGroupCommand;
use Ergonode\Attribute\Domain\Entity\AttributeId;
use Ergonode\Attribute\Domain\ValueObject\AttributeCode;
use Ergonode\Attribute\Domain\ValueObject\AttributeGroupCode;
use Ergonode\Attribute\Domain\ValueObject\AttributeType;

/**
 */
class ImportAttributeCommand extends Command
{
    private const NAME = 'ergonode:attributes:import';

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var AttriibuteQueryInterface
     */
    private $query;
    private $q2;

    /**
     * @param MessageBusInterface $messageBus
     * @param AttributeQueryInterface  $query
     */
    public function __construct(MessageBusInterface $messageBus, AttributeQueryInterface $query, 
        AttributeGroupQueryInterface $q2)
    {
        parent::__construct(static::NAME);

        $this->query = $query;
        $this->messageBus = $messageBus;
        $this->q2 = $q2;
    }

    /**
     * Command configuration
     */
    public function configure(): void
    {
        $this->setDescription('Importing Attributes');
        /*
        $this->addArgument('type', InputArgument::REQUIRED, 'type');
        $this->addArgument('code', InputArgument::REQUIRED, 'code');
        $this->addArgument('name', InputArgument::REQUIRED, 'name');
        $this->addArgument('label', InputArgument::REQUIRED, 'label');
        $this->addArgument('hint', InputArgument::REQUIRED, 'hint');
        $this->addArgument('placeholder', InputArgument::REQUIRED, 'placeholder');
        */
    }

    public function createGroup($code)
    {
        $label = $code;
        $code = str_replace(' ','_', strtolower($code));
        $code = new AttributeGroupCode($code);
        $isExists = $this->q2->checkAttributeGroupExistsByCode($code);
        if ($isExists)
        {
            return;
        }
        $command = new \Ergonode\Attribute\Domain\Command\Group\CreateAttributeGroupCommand(
            $code,
            new TranslatableString([
                'EN' => $label
            ])
        );
        $this->messageBus->dispatch($command);
    }

    public function loadGroups()
    {
        $language = new Language('EN');
        $rows = $this->q2->getAttributeGroups($language);
        $result = [];
        foreach($rows as $r)
        {
            $result[ $r['label'] ] = $r['id'];
        }
        return $result;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {

        $firstLine = true;
        $f = fopen(__DIR__ . '/Attributes.csv','r');
        $groups = $this->loadGroups();

        while($line = fgetcsv($f))
        {
            if ($firstLine)
            {
                $firstLine = FALSE;
                continue;
            }
            if (count($line) < 3)
            {
                continue;
            }
            if ($line[0] !== 'ADD')
            {
                continue;
            }

            $id = '';
            if (!isset($groups[ $line[5] ]))
            {
                $this->createGroup($line[5]);
                $groups = $this->loadGroups();
            }
            $id = $groups[$line[5]];

            $dat = [
                'code' => new AttributeCode($line[1]),
                'label' => new TranslatableString([
                        'EN' => $line[3],
                    ]),
                'placeholder' => new TranslatableString([
                        'EN' => $line[4],
                    ]),
                'hint' => new TranslatableString([
                        'EN' => '',
                    ]),
                'type' => new AttributeType($line[2]),
                'multilingual' => ! in_array($line[2], ['IMAGE','NUMERIC']),
                'groupid' => $id
            ];

            $isExists = $this->query->checkAttributeExistsByCode($dat['code']);
            if ($isExists)
            {
                continue;
            }
            $command = new \Ergonode\Attribute\Domain\Command\CreateAttributeCommand(
                $dat['type'],
                $dat['code'],
                $dat['label'],
                $dat['hint'],
                $dat['placeholder'],
                $dat['multilingual'],
                [$dat['groupid'] ]
            );
            $this->messageBus->dispatch($command);
            $output->writeln('<info>Attribute ' . $dat['label'] . ' created.</info>');
        }
        fclose($f);

        return 1;
    }
}
