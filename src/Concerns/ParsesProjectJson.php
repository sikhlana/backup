<?php

namespace Sikhlana\Backup\Concerns;

use Sikhlana\Backup\Exceptions\JsonValidationException;
use Sikhlana\Backup\Models\Project;
use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

trait ParsesProjectJson
{
    protected function parseProjectJson($filename)
    {
        $content = file_get_contents($filename);
        $data = json_decode($content);

        if (is_null($data) && $content !== 'null') {
            self::validateJsonSyntax($content, $filename);
        }

        $schemaFile = __DIR__ . '../../res/project-schema.json';

        if (strpos($schemaFile, '://') === false) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $schemaData = (object) ['$ref' => $schemaFile];

        $validator = new Validator();
        $validator->check($data, $schemaData);

        if (! $validator->isValid()) {
            $errors = [];

            foreach ((array) $validator->getErrors() as $error) {
                $errors[] = ($error['property'] ? $error['property'] . ' : ' : '') . $error['message'];
            }

            throw new JsonValidationException('`' . $filename . '` does not match the expected JSON schema', $errors);
        }

        return new Project((array) $data);
    }

    private static function validateJsonSyntax($json, $filename)
    {
        $parser = new JsonParser();
        $result = $parser->lint($json);

        if (is_null($result)) {
            if (defined('JSON_ERROR_UTF8') && JSON_ERROR_UTF8 === json_last_error()) {
                throw new \UnexpectedValueException('`' . $filename . '` is not UTF-8, could not parse as JSON.');
            }
        }

        throw new ParsingException('`' . $filename . '` does not contain valid JSON' . "\n" . $result->getMessage(), $result->getDetails());
    }
}