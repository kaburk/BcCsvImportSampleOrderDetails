<?php
declare(strict_types=1);

namespace BcCsvImportSampleOrderDetails\Controller\Admin;

use BcCsvImportCore\Controller\Admin\CsvImportsController;
use BcCsvImportCore\Service\CsvImportServiceInterface;
use BcCsvImportSampleOrderDetails\Service\SampleOrderDetailsCsvImportService;

/**
 * SampleOrderDetailsCsvImportsController
 *
 * 受注明細1対多CSVインポートコントローラー。
 * コアのテンプレートを使用し、タイトルと adminBase のみ差し替える。
 */
class SampleOrderDetailsCsvImportsController extends CsvImportsController
{

    /**
     * CSVアップロード画面
     *
     * コアのテンプレートを利用し、タイトルと adminBase のみ差し替える。
     *
     * @return void
     */
    public function index(): void
    {
        parent::index();
        $this->set('pageTitle', __d('baser_core', '受注明細CSVインポート サンプル'));
        $this->set('adminBase', '/baser/admin/bc-csv-import-sample-order-details/sample_order_details_csv_imports');
        $this->viewBuilder()->setTemplatePath($this->name);
        $this->viewBuilder()->setTemplate('BcCsvImportCore.Admin/CsvImports/index');
    }

    /**
     * インポートサービスを生成する
     *
     * @return CsvImportServiceInterface
     */
    protected function createImportService(): CsvImportServiceInterface
    {
        return new SampleOrderDetailsCsvImportService();
    }

}
