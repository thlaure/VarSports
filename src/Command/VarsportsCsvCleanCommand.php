<?php

namespace App\Command;

use App\Constant\Message;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'varsports:csv:clean',
    description: 'Clean the CSV file from all undesired data and save it into a JSON file',
)]
class VarsportsCsvCleanCommand extends Command
{
    public function __construct(
        private TranslatorInterface $translator,
        private string $filePath = 'docker/imports/clubs.csv',
        private string $cleanedFilePath = 'docker/imports/clubs_clean.json',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!file_exists($this->filePath)) {
            $io->error($this->translator->trans(Message::FILE_NOT_WRITABLE));

            return Command::FAILURE;
        }

        $dataAssoc = $this->createAssocArrayFromRawData();
        $clubsWithoutDupplicates = $this->removeDupplicates($dataAssoc);
        $cleanedClubs = $this->cleanData($clubsWithoutDupplicates);
        $this->saveCleanedDataIntoJson($cleanedClubs);

        $io->success('Success');

        return Command::SUCCESS;
    }

    /**
     * @return array<array<string, string>>
     */
    private function createAssocArrayFromRawData(): array
    {
        $header = ['id', 'name', 'email', 'slug', 'description', 'lastname', 'firstname', 'facebook', 'instagram', 'youtube', 'twitter', 'website', 'address', 'postal_code', 'city', 'phone', 'admin_email', 'disciplines'];

        $file = fopen($this->filePath, 'r');
        if (false === $file) {
            throw new NotFoundHttpException($this->translator->trans(Message::FILE_NOT_READABLE));
        }

        $dataAssoc = [];
        while (($data = fgetcsv($file)) !== false) {
            $dataAssoc[] = array_combine($header, $data);
        }
        fclose($file);

        return $dataAssoc;
    }

    /**
     * @param array<array<string, string>> $dataAssoc
     *
     * @return array<array<string, string>>
     */
    private function removeDupplicates(array $dataAssoc): array
    {
        $clubsByEmail = [];
        foreach ($dataAssoc as $club) {
            $clubsByEmail[$club['admin_email']] = $club;
        }

        return array_values($clubsByEmail);
    }

    /**
     * @param array<array<string, string>> $dataToClean
     *
     * @return array<int<0, max>, array<string, mixed>>
     */
    private function cleanData(array $dataToClean): array
    {
        $clubs = [];
        foreach ($dataToClean as $club) {
            if (!$club['admin_email']) {
                continue;
            }

            foreach ($club as $key => $value) {
                if ('' === $value) {
                    $club[$key] = null;
                }
            }

            if (!empty($club['phone'])) {
                $phoneReplace = (string) intval(str_replace([' ', '-', '.'], '', $club['phone']));
                if ('33' === substr($phoneReplace, 0, 2)) {
                    $club['phone'] = '0'.substr($phoneReplace, 2);
                }

                $club['phone'] = !empty($club['phone']) ? '0'.$phoneReplace : null;
            } else {
                $club['phone'] = null;
            }

            $club['disciplines'] = is_string($club['disciplines']) && preg_match('/\{/', $club['disciplines']) ? unserialize($club['disciplines']) : [$club['disciplines']];

            if (is_string($club['address'])) {
                $club['address'] = ucwords(strtolower($club['address']), ' -');
            } else {
                $club['address'] = null;
            }

            if (is_string($club['city'])) {
                $club['city'] = ucwords(strtolower($club['city']), ' -');
            } else {
                $club['city'] = null;
            }

            if (is_string($club['lastname'])) {
                $club['lastname'] = ucwords(strtolower($club['lastname']), ' -');
            } else {
                $club['lastname'] = null;
            }

            if (is_string($club['firstname'])) {
                $club['firstname'] = ucwords(strtolower($club['firstname']), ' -');
            } else {
                $club['firstname'] = null;
            }

            $clubs[] = $club;
        }

        return $clubs;
    }

    /**
     * @param array<int<0, max>, array<string, mixed>> $cleanedClubs
     */
    private function saveCleanedDataIntoJson(array $cleanedClubs): void
    {
        $cleanedFile = fopen($this->cleanedFilePath, 'w');
        if (false === $cleanedFile) {
            throw new NotFoundHttpException($this->translator->trans(Message::FILE_NOT_FOUND));
        }

        $jsonData = json_encode($cleanedClubs, JSON_PRETTY_PRINT);
        if (false === $jsonData) {
            throw new \JsonException($this->translator->trans(Message::GENERIC_ERROR));
        }

        fwrite($cleanedFile, $jsonData);
        fclose($cleanedFile);
    }
}
