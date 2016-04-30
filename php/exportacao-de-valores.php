<?php
require_once("custom/php/common.php");
require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';
/**
 * Class that handle all the methods that are necessary to execute this component
 */
class ExportValues{
    private $db;            // Object from DB_Op that contains the access to the database

    /**
     * Constructor method
     */
    public function __construct(){
        $this->db = new Db_Op();
    }
    
    /**
     * This method creates the Excel file with the results of the dynamic search.
     * @param string $querydinamica (string with the query dynamicly created in the dynamic search)
     * @param string $frase (string with the setence that describes the search)
     * @param array $arrayId (array with id of the properties used in th dynamic search)
     * @param array $arrayNomes (array with names of the properties used in th dynamic search)
     * @param array $arrayValores (array with values of the properties used in th dynamic search)
     * @param array $arrayInstId (array with id of the instances from the result of the dynamic search)
     * @param array $arrayInst (array with names of the instances from the result of the dynamic search)
     */
    public function geraExcel($querydinamica,$frase,$arrayId,$arrayNomes, $arrayValores,$arrayInstId,$arrayInst) {
        // Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("")
                                     ->setLastModifiedBy("")
                                     ->setTitle("")
                                     ->setSubject("")
                                     ->setDescription("")
                                     ->setKeywords("")
                                     ->setCategory("");

	//On the first line we will put the setence that describes the search
	$linha = 1;
	$coluna = 'A';
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $frase);	 

	//The second line will have the names form-field-name as headers
        $linha = 2;
        $coluna = 'A';

	if (count($arrayId) === 0) {
            
            for($i = 0; $i < count($arrayId); $i++)
            {
                $get_form_field_name = "SELECT form_field_name FROM property WHERE id = ".$arrayId[$i];
                $fieldformnames = $this->db->runQuery($get_form_field_name);
                while($names = $fieldformnames->fetch_assoc())
                {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $names['form_field_name']);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                    $coluna++;
                }
            }

            $percorre = 0;
            $linha = 3;
            $coluna = 'A';
            while($percorre < count($arrayNomes) )
            {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $arrayNomes[$percorre]);
                $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                $coluna++;
                $percorre++;
            }

            $percorre = 0;
            $linha = 4;
            $coluna = 'A';
            while($percorre < count($arrayValores) )
            {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $arrayValores[$percorre]);
                $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                $coluna++;
                $percorre++;
            }
            $linha = 6;
            $coluna1 = 'A';
            $coluna2 = 'B';
        }
        else {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, "Não foram selecionadas quaisquer propriedades como filtro.");
            $linha = 4;
            $coluna1 = 'A';
            $coluna2 = 'B';
        }
	


        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna1.$linha,'ID');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna2.$linha,'Instâncias');

	$percorre = 0;
	$linha++;
	$coluna = 'A';
	while($percorre < count($arrayInst))
	{
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha,$arrayInstId[$percorre]);
            $coluna++;
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha,$arrayInst[$percorre]);
            $coluna = 'A';
            $linha++;
            $percorre++;
	}
	// a terceira linha os nomes em si das propriedades.

	$objPHPExcel->getActiveSheet()->setTitle('Simple');


	// Set active sheet index to the first sheet, so Excel opens this as the first sheet

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	//Link para download do xlsx gerado
	echo '<a href="/ExportValues.xlsx" target="_blank">Clique aqui para descarregar</a>';
	$objWriter->save(getcwd()."/ExportValues.xlsx");
	//$objWriter->save('php://output');
    }
}