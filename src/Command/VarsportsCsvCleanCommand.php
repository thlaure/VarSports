<?php

namespace App\Command;

use App\Constant\Message;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsCommand(
    name: 'varsports:csv:clean',
    description: 'Clean the CSV file from all undesired data and save it into a JSON file',
)]
class VarsportsCsvCleanCommand extends Command
{
    public function __construct(
        private string $filePath = 'docker/imports/clubs.csv',
        private string $cleanedFilePath = 'docker/imports/clubs_clean.json'
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!file_exists($this->filePath)) {
            $io->error('File not found');

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
            throw new NotFoundHttpException(Message::FILE_NOT_FOUND);
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
            $clubsByEmail[$club['email']] = $club;
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
            foreach ($club as $key => $value) {
                if ('' === $value) {
                    $club[$key] = null;
                }
            }

            $club['phone'] = !empty($club['phone']) ? '0'.intval(str_replace([' ', '-', '.'], '', $club['phone'])) : null;

            $club['disciplines'] = is_string($club['disciplines']) && preg_match('/\{/', $club['disciplines']) ? unserialize($club['disciplines']) : $club['disciplines'];

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
            throw new NotFoundHttpException(Message::FILE_NOT_FOUND);
        }

        $jsonData = json_encode($cleanedClubs, JSON_PRETTY_PRINT);
        if (false === $jsonData) {
            throw new \JsonException(Message::GENERIC_ERROR);
        }

        fwrite($cleanedFile, $jsonData);
        fclose($cleanedFile);
    }
}
