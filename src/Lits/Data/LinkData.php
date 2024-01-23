<?php

declare(strict_types=1);

namespace Lits\Data;

use Hashids\Hashids;
use Lits\Config\ShortenerConfig;
use Lits\Database;
use Lits\Exception\InvalidDataException;
use Lits\Settings;
use Safe\Exceptions\PcreException;

use function Latitude\QueryBuilder\field;
use function Sabre\Uri\normalize;
use function Safe\preg_match;

final class LinkData extends DatabaseData
{
    public int $id;
    public string $link;

    /** @throws InvalidDataException */
    public static function fromHash(
        string $hash,
        Settings $settings,
        Database $database,
    ): self {
        $decoded = self::hashids()->decode($hash);

        if (!isset($decoded[0])) {
            throw new InvalidDataException('Could not decode hash');
        }

        return self::fromId($decoded[0], $settings, $database);
    }

    /** @throws InvalidDataException */
    public static function fromId(
        int $id,
        Settings $settings,
        Database $database,
    ): self {
        $link = new static($settings, $database);
        $link->id = $id;

        $statement = $database->execute(
            $database->query
                ->select('link')
                ->from('link')
                ->where(field('id')->eq($id))
                ->limit(1),
        );

        $result = $statement->fetchColumn();

        if (!\is_string($result)) {
            throw new InvalidDataException('Could not find link');
        }

        $link->link = $result;

        return $link;
    }

    /**
     * @throws \PDOException
     * @throws InvalidDataException
     */
    public static function fromLink(
        string $link,
        Settings $settings,
        Database $database,
    ): self {
        $link = self::validate($link, $settings);

        $id = $database->insertIgnore('link', ['link' => $link], [], 'id');

        if (!\is_int($id)) {
            throw new InvalidDataException('Could not find ID');
        }

        $result = new self($settings, $database);
        $result->id = $id;
        $result->link = $link;

        return $result;
    }

    /** @throws InvalidDataException */
    public function hash(): string
    {
        return self::hashids()->encode($this->id);
    }

    /** @throws InvalidDataException */
    private static function hashids(): Hashids
    {
        try {
            return new Hashids();
        } catch (\Throwable $exception) {
            throw new InvalidDataException(
                'Could not process hash',
                0,
                $exception,
            );
        }
    }

    /** @throws InvalidDataException */
    private static function validate(string $link, Settings $settings): string
    {
        \assert($settings['shortener'] instanceof ShortenerConfig);

        try {
            $link = normalize($link);

            self::validateAllow($link, $settings['shortener']->link_allow);
            self::validateDeny($link, $settings['shortener']->link_deny);
        } catch (\Throwable $exception) {
            throw new InvalidDataException(
                'Could not validate link URL',
                0,
                $exception,
            );
        }

        return $link;
    }

    /**
     * @param list<string> $deny
     * @throws InvalidDataException
     * @throws PcreException
     */
    private static function validateDeny(string $link, array $deny): void
    {
        foreach ($deny as $preg) {
            if (preg_match($preg, $link) === 1) {
                throw new InvalidDataException('Link URL was denied');
            }
        }
    }

    /**
     * @param list<string> $allow
     * @throws InvalidDataException
     * @throws PcreException
     */
    private static function validateAllow(string $link, array $allow): void
    {
        foreach ($allow as $preg) {
            if (preg_match($preg, $link) === 1) {
                return;
            }
        }

        throw new InvalidDataException('Link URL was not allowed');
    }
}
