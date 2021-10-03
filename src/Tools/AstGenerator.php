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
                'Assign' => [
                    'Token $name',
                    'Expr $value',
                ],
                'Binary' => [
                    'Expr $left',
                    'Token $operator',
                    'Expr $right',
                ],
                'Grouping' => [
                    'Expr $expression'
                ],
                'Literal' => [
                    'mixed $value'
                ],
                'Unary' => [
                    'Token $operator',
                    'Expr $right'
                ],
                'Variable' => [
                    'Token $name'
                ]
            ]
        );

        $this->defineAst(
            'Stmt',
            [
                'Block' => [
                    'array $statements',
                ],
                'Expression' => [
                    'Expr $expression',
                ],
                'Prnt' => [
                    'Expr $expression',
                ],
                'Vari' => [
                    'Token $name',
                    'Expr $initializer'
                ]
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

        $nameSpace = sprintf('%s\%s', 'Phlox', $baseName);

        if (!is_dir($subDirectoryPath)) {
            mkdir($subDirectoryPath);
        }

        $file = fopen($path, 'w');
        $this->defineBaseClass($file, $baseName);
        fclose($file);

        $visitorPath = $this->outputDir . "/{$baseName}Visitor.php";
        $file = fopen($visitorPath, 'w');
        $this->defineVisitor($file, $baseName, $types);
        fclose($file);

        foreach ($types as $class => $fields) {
            $classPath = $subDirectoryPath . '/' . $class . '.php';
            $file = fopen($classPath, 'w');

            $this->writeFileHeader($file, $nameSpace, $class, $baseName);
            $this->writeConstructor($file, $fields);
            $this->writeConcreteAcceptMethod($file, $class, $baseName);
            $this->writeFooter($file);

            fclose($file);
        }
    }

    protected function writeFileHeader(
        $file,
        string $namespace,
        string $className,
        string $extends = null,
        bool $isAbstract = false,
        bool $isInterface = false
    ): void {
        fwrite($file, "<?php\n\n");
        fwrite($file, "declare(strict_types=1);\n\n");
        fwrite($file, "namespace {$namespace};\n\n");

        $type = $isInterface ? 'interface' : 'class';

        $classString = "{$type} {$className}\n{\n";

        if ($extends !== null) {
            $classString = "class {$className} extends {$extends}\n{\n";
        }

        if ($isAbstract) {
            $classString = 'abstract ' . $classString;
        }

        fwrite($file, $classString);
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

    private function writeBlankLine($file, int $times = 1): void
    {
        for ($i = 0; $i < $times; $i++) {
            fwrite($file, "\n");
        }
    }

    private function writeAbstractAcceptMethod($file, string $baseName): void
    {
        fwrite($file, "    public abstract function accept({$baseName}Visitor \$visitor);");
    }

    private function writeConcreteAcceptMethod(
        $file,
        string $className,
        string $baseName
    ): void {
        fwrite($file, "    public function accept({$baseName}Visitor \$visitor)\n");
        fwrite($file, "    {\n");
        fwrite($file, "        return \$visitor->visit{$className}{$baseName}(\$this);\n");
        fwrite($file, "    }\n");
    }

    private function defineVisitor(
        $file,
        string $baseName,
        array $types
    ): void {
        $this->writeFileHeader($file, 'Phlox', "{$baseName}Visitor", null, false, true);

        foreach ($types as $class => $fields) {
            $functionName = sprintf(
                "    public function visit%s%s($%s);",
                $class,
                $baseName,
                mb_strtolower($baseName)
            );
            fwrite($file, $functionName);
            $this->writeBlankLine($file, 2);
        }

        $this->writeFooter($file);
    }

    protected function defineBaseClass($file, string $baseName): void
    {
        $this->writeFileHeader($file, 'Phlox', $baseName, null, true);
        $this->writeAbstractAcceptMethod($file, $baseName);
        $this->writeBlankLine($file);
        $this->writeFooter($file);
    }
}
