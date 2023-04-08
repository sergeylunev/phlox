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
                new AstDto(
                    name: 'Assign',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Token $name',
                        'Expr $value',
                    ]
                ),
                new AstDto(
                    name: 'Binary',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Expr $left',
                        'Token $operator',
                        'Expr $right',
                    ]
                ),
                new AstDto(
                    name: 'Call',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Expr $callee',
                        'Token $paren',
                        'array $arguments',
                    ]
                ),
                new AstDto(
                    name: 'Get',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Expr $object',
                        'Token $name'
                    ]
                ),
                new AstDto(
                    name: 'Grouping',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                    ],
                    types: [
                        'Expr $expression',
                    ]
                ),
                new AstDto(
                    name: 'Literal',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                    ],
                    types: [
                        'mixed $value'
                    ]
                ),
                new AstDto(
                    name: 'Logical',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Expr $left',
                        'Token $operator',
                        'Expr $right',
                    ]
                ),
                new AstDto(
                    name: 'Unary',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Token $operator',
                        'Expr $right'
                    ]
                ),
                new AstDto(
                    name: 'Variable',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\ExprVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Token $name'
                    ]
                ),
            ]
        );

        $this->defineAst(
            'Stmt',
            [
                new AstDto(
                    name: 'Block',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor"
                    ],
                    types: [
                        'array $statements',
                    ]
                ),
                new AstDto(
                    name: 'Clas',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Token $name',
                        'array $methods',
                    ]
                ),
                new AstDto(
                    name: 'Expression',
                    imports: [
                        "Phlox\Expr",
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor"
                    ],
                    types: [
                        'Expr $expression',
                    ]
                ),
                new AstDto(
                    name: 'Fun',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor",
                        "Phlox\Token",
                    ],
                    types: [
                        'Token $name',
                        'array $params',
                        'array $body',
                    ]
                ),
                new AstDto(
                    name: 'Fi',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor",
                        "Phlox\Expr",

                    ],
                    types: [
                        'Expr $condition',
                        'Stmt $thenBranch',
                        '?Stmt $elseBranch'
                    ]
                ),
                new AstDto(
                    name: 'Prnt',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor",
                        "Phlox\Expr",
                    ],
                    types: [
                        'Expr $expression',
                    ]
                ),
                new AstDto(
                    name: 'Rtrn',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor",
                        "Phlox\Expr",
                        "Phlox\Token",
                    ],
                    types: [
                        'Token $keyword',
                        'Expr $value',
                    ]
                ),
                new AstDto(
                    name: 'Vari',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor",
                        "Phlox\Expr",
                        "Phlox\Token",
                    ],
                    types: [
                        'Token $name',
                        'Expr $initializer'
                    ]
                ),
                new AstDto(
                    name: 'Whle',
                    imports: [
                        "Phlox\Stmt",
                        "Phlox\StmtVisitor",
                        "Phlox\Expr",
                    ],
                    types: [
                        'Expr $condition',
                        'Stmt $body'
                    ]
                ),
            ]
        );

    }

    /**
     * @param string $baseName
     * @param AstDto[] $types
     * @return void
     */
    protected function defineAst(
        string $baseName,
        array  $types
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

        foreach ($types as $type) {
            $classPath = $subDirectoryPath . '/' . $type->name . '.php';
            $file = fopen($classPath, 'w');

            $this->writeFileHeader($file, $nameSpace, $type->name, $baseName, false, false, $type->imports);
            $this->writeConstructor($file, $type->types);
            $this->writeConcreteAcceptMethod($file, $type->name, $baseName);
            $this->writeFooter($file);

            fclose($file);
        }
    }

    protected function defineBaseClass($file, string $baseName): void
    {
        $this->writeFileHeader($file, 'Phlox', $baseName, null, true);
        $this->writeAbstractAcceptMethod($file, $baseName);
        $this->writeBlankLine($file);
        $this->writeFooter($file);
    }

    protected function writeFileHeader(
        $file,
        string $namespace,
        string $className,
        string $extends = null,
        bool $isAbstract = false,
        bool $isInterface = false,
        array $imports = []
    ): void
    {
        fwrite($file, "<?php\n\n");
        fwrite($file, "declare(strict_types=1);\n\n");
        fwrite($file, "namespace {$namespace};\n\n");

        foreach ($imports as $import) {
            fwrite($file, "use {$import};\n");
        }

        $this->writeBlankLine($file);

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

    private function writeAbstractAcceptMethod($file, string $baseName): void
    {
        fwrite($file, "    public abstract function accept({$baseName}Visitor \$visitor);");
    }

    private function writeBlankLine($file, int $times = 1): void
    {
        for ($i = 0; $i < $times; $i++) {
            fwrite($file, "\n");
        }
    }

    protected function writeFooter($file): void
    {
        fwrite($file, "}\n");
    }

    private function defineVisitor(
        $file,
        string $baseName,
        array $types
    ): void
    {
        $methods = [];
        $imports = [];

        /** @var AstDto $type */
        foreach ($types as $type) {
            $functionName = sprintf(
                "    public function visit%s%s(%s $%s);",
                $type->name,
                $baseName,
                $type->name,
                mb_strtolower($baseName)
            );
            $methods[] = $functionName;
            $imports[] = "Phlox\\{$baseName}\\{$type->name}";
        }

        $this->writeFileHeader(
            $file, 'Phlox', "{$baseName}Visitor", null, false, true, $imports
        );

        foreach ($methods as $method) {
            fwrite($file, $method);
            $this->writeBlankLine($file, 2);
        }

        $this->writeFooter($file);
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

    private function writeConcreteAcceptMethod(
        $file,
        string $className,
        string $baseName
    ): void
    {
        fwrite($file, "    public function accept({$baseName}Visitor \$visitor)\n");
        fwrite($file, "    {\n");
        fwrite($file, "        return \$visitor->visit{$className}{$baseName}(\$this);\n");
        fwrite($file, "    }\n");
    }
}
