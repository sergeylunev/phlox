<?php

declare(strict_types=1);

namespace Phlox\Tools;

class AstGenerator
{
    private string $outputDir;

    public function __construct(string $outputDir)
    {
        if (!is_dir($outputDir)) {
            throw new \Exception(
                sprintf('%s is not a valid directory', $outputDir)
            );
        }

        $this->outputDir = $outputDir;
    }

    public function generate(): void
    {
        $this->defineAst(
            'Expr',
            [
                'Binary' => [
                    'Expr $left',
                    'Token $operator',
                    'Expr $right',
                ],
                'Grouping' => [
                    'Expr $expression'
                ],
                'Literal' => [
                    'Object $value'
                ],
                'Unary' => [
                    'Token $operator',
                    'Expr $right'
                ],
            ]
        );

    }

    protected function defineAst(
        string $baseName,
        array $types
    ): void
    {
        $path = $this->outputDir . '/' . $baseName . '.php';
        $subDirectoryPath = $this->outputDir . '/' . $baseName;

        if (!is_dir($subDirectoryPath)) {
            mkdir($subDirectoryPath);
        }

        $file = fopen($path, 'w');

        $this->writeFileHeader($file, 'Phlox', $baseName);
        $this->writeFooter($file);

        fclose($file);

        foreach ($types as $class => $fields) {
            $classPath = $subDirectoryPath . '/' . $class . '.php';
            $file = fopen($classPath, 'w');
            $nameSpace = sprintf('%s\%s', 'Phlox', $baseName);

            $this->writeFileHeader($file, $nameSpace, $class, $baseName);
            $this->writeConstructor($file, $fields);
            $this->writeFooter($file);

            fclose($file);
        }
    }

    protected function writeFileHeader(
        $file,
        string $namespace,
        string $className,
        string $extends = null
    ): void {
        fwrite($file, "<?php\n\n");
        fwrite($file, "declare(strict_types=1);\n\n");
        fwrite($file, "namespace {$namespace};\n\n");

        if ($extends) {
            fwrite($file, "class {$className} extends {$extends}\n{\n");
        } else {
            fwrite($file, "class {$className}\n{\n");
        }
    }

    protected function writeFooter($file): void
    {
        fwrite($file, "}\n");
    }

    private function writeConstructor($file, mixed $fields): void
    {
        foreach ($fields as $field) {
            fwrite($file, sprintf(
                "    public %s;\n",
                $field
            ));
        }

        $this->writeBlankLine($file);
        fwrite(
            $file,
            sprintf(
                "    public function __construct(%s)\n    {\n",
                implode(', ', $fields)
            )
        );

        foreach ($fields as $field) {
            $name = mb_substr(explode(' ', $field)[1], 1);

            fwrite($file, '        $this->' . $name . ' = ' . '$' . $name . ";\n");
        }

        fwrite($file, "    }\n");
    }

    private function writeBlankLine($file): void
    {
        fwrite($file, "\n");
    }
}
