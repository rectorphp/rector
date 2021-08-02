# 14 Rules Overview

## AddRemovedDefaultValuesRector

Complete removed default values explicitly

- class: [`Rector\PHPOffice\Rector\StaticCall\AddRemovedDefaultValuesRector`](../src/Rector/StaticCall/AddRemovedDefaultValuesRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $logger = new \PHPExcel_CalcEngine_Logger;
-        $logger->setWriteDebugLog();
+        $logger->setWriteDebugLog(false);
     }
 }
```

<br>

## CellStaticToCoordinateRector

Methods to manipulate coordinates that used to exists in PHPExcel_Cell to PhpOffice\PhpSpreadsheet\Cell\Coordinate

- class: [`Rector\PHPOffice\Rector\StaticCall\CellStaticToCoordinateRector`](../src/Rector/StaticCall/CellStaticToCoordinateRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
-        \PHPExcel_Cell::stringFromColumnIndex();
+        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex();
     }
 }
```

<br>

## ChangeChartRendererRector

Change chart renderer

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeChartRendererRector`](../src/Rector/StaticCall/ChangeChartRendererRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_Settings::setChartRenderer($rendererName, $rendererLibraryPath);
+        \PHPExcel_Settings::setChartRenderer(\PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph::class);
     }
 }
```

<br>

## ChangeConditionalGetConditionRector

Change argument `PHPExcel_Style_Conditional->getCondition()` to `getConditions()`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector`](../src/Rector/MethodCall/ChangeConditionalGetConditionRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $conditional = new \PHPExcel_Style_Conditional;
-        $someCondition = $conditional->getCondition();
+        $someCondition = $conditional->getConditions()[0] ?? '';
     }
 }
```

<br>

## ChangeConditionalReturnedCellRector

Change conditional call to `getCell()`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalReturnedCellRector`](../src/Rector/MethodCall/ChangeConditionalReturnedCellRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $cell = $worksheet->setCellValue('A1', 'value', true);
+        $cell = $worksheet->getCell('A1')->setValue('value');
     }
 }
```

<br>

## ChangeConditionalSetConditionRector

Change argument `PHPExcel_Style_Conditional->setCondition()` to `setConditions()`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector`](../src/Rector/MethodCall/ChangeConditionalSetConditionRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $conditional = new \PHPExcel_Style_Conditional;
-        $someCondition = $conditional->setCondition(1);
+        $someCondition = $conditional->setConditions((array) 1);
     }
 }
```

<br>

## ChangeDataTypeForValueRector

Change argument `DataType::dataTypeForValue()` to DefaultValueBinder

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeDataTypeForValueRector`](../src/Rector/StaticCall/ChangeDataTypeForValueRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        $type = \PHPExcel_Cell_DataType::dataTypeForValue('value');
+        $type = \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder::dataTypeForValue('value');
     }
 }
```

<br>

## ChangeDuplicateStyleArrayToApplyFromArrayRector

Change method call `duplicateStyleArray()` to `getStyle()` + `applyFromArray()`

- class: [`Rector\PHPOffice\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector`](../src/Rector/MethodCall/ChangeDuplicateStyleArrayToApplyFromArrayRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->duplicateStyleArray($styles, $range, $advanced);
+        $worksheet->getStyle($range)->applyFromArray($styles, $advanced);
     }
 }
```

<br>

## ChangeIOFactoryArgumentRector

Change argument of `PHPExcel_IOFactory::createReader()`, `PHPExcel_IOFactory::createWriter()` and `PHPExcel_IOFactory::identify()`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeIOFactoryArgumentRector`](../src/Rector/StaticCall/ChangeIOFactoryArgumentRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        $writer = \PHPExcel_IOFactory::createWriter('CSV');
+        $writer = \PHPExcel_IOFactory::createWriter('Csv');
     }
 }
```

<br>

## ChangePdfWriterRector

Change init of PDF writer

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangePdfWriterRector`](../src/Rector/StaticCall/ChangePdfWriterRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_Settings::setPdfRendererName(PHPExcel_Settings::PDF_RENDERER_MPDF);
-        \PHPExcel_Settings::setPdfRenderer($somePath);
-        $writer = \PHPExcel_IOFactory::createWriter($spreadsheet, 'PDF');
+        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
     }
 }
```

<br>

## ChangeSearchLocationToRegisterReaderRector

Change argument `addSearchLocation()` to `registerReader()`

- class: [`Rector\PHPOffice\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector`](../src/Rector/StaticCall/ChangeSearchLocationToRegisterReaderRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
-        \PHPExcel_IOFactory::addSearchLocation($type, $location, $classname);
+        \PhpOffice\PhpSpreadsheet\IOFactory::registerReader($type, $classname);
     }
 }
```

<br>

## GetDefaultStyleToGetParentRector

Methods to (new `Worksheet())->getDefaultStyle()` to `getParent()->getDefaultStyle()`

- class: [`Rector\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector`](../src/Rector/MethodCall/GetDefaultStyleToGetParentRector.php)

```diff
 class SomeClass
 {
     public function run()
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->getDefaultStyle();
+        $worksheet->getParent()->getDefaultStyle();
     }
 }
```

<br>

## IncreaseColumnIndexRector

Column index changed from 0 to 1 - run only ONCE! changes current value without memory

- class: [`Rector\PHPOffice\Rector\MethodCall\IncreaseColumnIndexRector`](../src/Rector/MethodCall/IncreaseColumnIndexRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $worksheet = new \PHPExcel_Worksheet();
-        $worksheet->setCellValueByColumnAndRow(0, 3, '1150');
+        $worksheet->setCellValueByColumnAndRow(1, 3, '1150');
     }
 }
```

<br>

## RemoveSetTempDirOnExcelWriterRector

Remove `setTempDir()` on PHPExcel_Writer_Excel5

- class: [`Rector\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector`](../src/Rector/MethodCall/RemoveSetTempDirOnExcelWriterRector.php)

```diff
 final class SomeClass
 {
     public function run(): void
     {
         $writer = new \PHPExcel_Writer_Excel5;
-        $writer->setTempDir();
     }
 }
```

<br>
