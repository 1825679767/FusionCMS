<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Entity\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\Exceptions\HasExitCodeInterface;

/**
 * CastException is thrown for invalid cast initialization and management.
 */
class CastException extends FrameworkException implements HasExitCodeInterface
{
    public function getExitCode(): int
    {
        return EXIT_CONFIG;
    }

    /**
     * Thrown when the cast class does not extends BaseCast.
     *
     * @return static
     */
    public static function forInvalidInterface(string $class)
    {
        return new static('The "' . $class . '" class must inherit the "CodeIgniter\Entity\Cast\BaseCast" class.');
    }

    /**
     * Thrown when the Json format is invalid.
     *
     * @return static
     */
    public static function forInvalidJsonFormat(int $error)
    {
        switch ($error) {
            case JSON_ERROR_DEPTH:
                return new static('Maximum stack depth exceeded.');

            case JSON_ERROR_STATE_MISMATCH:
                return new static('Underflow or the modes mismatch.');

            case JSON_ERROR_CTRL_CHAR:
                return new static('Unexpected control character found.');

            case JSON_ERROR_SYNTAX:
                return new static('Syntax error, malformed JSON.');

            case JSON_ERROR_UTF8:
                return new static('Malformed UTF-8 characters, possibly incorrectly encoded.');

            default:
                return new static('Unknown error.');
        }
    }

    /**
     * Thrown when the cast method is not `get` or `set`.
     *
     * @return static
     */
    public static function forInvalidMethod(string $method)
    {
        return new static('The "' . $method . '" is invalid cast method, valid methods are: ["get", "set"].');
    }

    /**
     * Thrown when the casting timestamp is not correct timestamp.
     *
     * @return static
     */
    public static function forInvalidTimestamp()
    {
        return new static('Type casting "timestamp" expects a correct timestamp.');
    }
}
