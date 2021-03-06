<?php namespace Helstern\Nomsky\Grammars\Ebnf\Graphviz;

use Helstern\Nomsky\Grammars\Ebnf\IsoEbnf\LexerFactory as IsoEbnfLexerFactory;
use Helstern\Nomsky\Grammars\Ebnf\IsoEbnf\Parser as IsoEbnfParser;
use Helstern\Nomsky\Grammars\TestOutput;
use Helstern\Nomsky\Grammars\TestResources;
use Helstern\Nomsky\Graphviz\LocalFSDotFile;
use Helstern\Nomsky\Parser\Errors\ParseAssertions;
use Helstern\Nomsky\Tokens\TokenPredicates;

class AstWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $fileName
     * @param bool $deleteExisting
     * @return \SplFileInfo
     */
    static public function createOutputFile($fileName, $deleteExisting = true)
    {
        /** @var \SplFileInfo $fileInfo */
        $fileInfo = null;

        $output = new TestOutput();
        if ($output->fileExists($fileName)) {
            if ($deleteExisting) {
                $output->deleteFile($fileName);
                $fileInfo = $output->createFile($fileName);
            } else {
                $fileInfo = $output->getFileInfo($fileName);
            }
        } else {
            $fileInfo = $output->createFile($fileName);
        }

        return $fileInfo;
    }

    /**
     * @param $fileName
     * @param bool $deleteExisting
     *
     * @return LocalFSDotFile
     */
    static public function createLocalFSDotFile($fileName, $deleteExisting = true) {
        $outputFileInfo = self::createOutputFile($fileName, (bool) $deleteExisting);
        $dotFile = new LocalFSDotFile($outputFileInfo);

        return $dotFile;
    }

    /**
     * @param $fileName
     * @return string
     */
    static public function getOutputFileContents($fileName)
    {
        $output = new TestOutput();
        if ($output->fileExists($fileName)) {
            $contents = $output->getContents($fileName);
            return $contents;
        }

        return '';
    }

    /**
     * @param string $fileName
     * @return string
     */
    static public function getResourceFilePath($fileName)
    {
        $resource = new TestResources();
        return $resource->getResourceFilePath($fileName);
    }

    /**
     * @group milestone
     */
    public function testWriteDotFile()
    {
        $grammarFile = self::getResourceFilePath('ebnf.iso.ebnf');
        $lexer = (new IsoEbnfLexerFactory())->fromFile($grammarFile);

        $assertions = new ParseAssertions(new TokenPredicates);
        $parser = new IsoEbnfParser($assertions);

        $syntaxNode = $parser->parse($lexer);
        $dotFile = self::createLocalFSDotFile('ebnf.iso.graphviz', $deleteExisting = true);

        $astWriter = new AstWriter();
        $astWriter->write($syntaxNode, $dotFile);

        $actualFile = self::getOutputFileContents('ebnf.iso.graphviz');
        $this->assertNotEmpty($actualFile);
    }
}
