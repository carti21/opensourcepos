<?php

namespace App\Controllers;

use app\Models\Tax_jurisdiction;

/**
 *
 *
 * @property tax_jurisdiction tax_jurisdiction
 *
 */
class Tax_jurisdictions extends Secure_Controller
{
	public function __construct()
	{
		parent::__construct('tax_jurisdictions');

		$this->tax_jurisdiction = model('Tax_jurisdiction');

		helper('tax_helper');
	}


	public function index(): void
	{
		 $data['table_headers'] = $this->xss_clean(get_tax_jurisdictions_table_headers());

		 echo view('taxes/tax_jurisdictions', $data);
	}

	/*
	 * Returns tax_category table data rows. This will be called with AJAX.
	 */
	public function search(): void
	{
		$search = $this->request->getGet('search');
		$limit  = $this->request->getGet('limit');
		$offset = $this->request->getGet('offset');
		$sort   = $this->request->getGet('sort');
		$order  = $this->request->getGet('order');

		$tax_jurisdictions = $this->tax_jurisdiction->search($search, $limit, $offset, $sort, $order);
		$total_rows = $this->tax_jurisdiction->get_found_rows($search);

		$data_rows = [];
		foreach($tax_jurisdictions->getResult() as $tax_jurisdiction)
		{
			$data_rows[] = $this->xss_clean(get_tax_jurisdictions_data_row($tax_jurisdiction));
		}

		echo json_encode (['total' => $total_rows, 'rows' => $data_rows]);
	}

	public function get_row(int $row_id): void
	{
		$data_row = $this->xss_clean(get_tax_jurisdictions_data_row($this->tax_jurisdiction->get_info($row_id)));

		echo json_encode($data_row);
	}

	public function view(int $tax_jurisdiction_id = -1): void	//TODO: Replace -1 with constant
	{
		$data['tax_jurisdiction_info'] = $this->tax_jurisdiction->get_info($tax_jurisdiction_id);

		echo view("taxes/tax_jurisdiction_form", $data);
	}


	public function save(int $jurisdiction_id = -1): void	//TODO: Replace -1 with constant
	{
		$tax_jurisdiction_data = [
			'jurisdiction_name' => $this->request->getPost('jurisdiction_name'),
			'reporting_authority' => $this->request->getPost('reporting_authority')
		];

		if($this->tax_jurisdiction->save($tax_jurisdiction_data))
		{
			$tax_jurisdiction_data = $this->xss_clean($tax_jurisdiction_data);

			if($jurisdiction_id == -1)	//TODO: Replace -1 with constant
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Tax_jurisdictions.successful_adding'),
					'id' => $tax_jurisdiction_data['jurisdiction_id']
				]);
			}
			else
			{
				echo json_encode ([
					'success' => TRUE,
					'message' => lang('Tax_jurisdictions.successful_updating'),
					'id' => $jurisdiction_id
				]);
			}
		}
		else
		{
			echo json_encode ([
				'success' => FALSE,
				'message' => lang('Tax_jurisdictions.error_adding_updating') . ' ' . $tax_jurisdiction_data['jurisdiction_name'],
				'id' => -1
			]);
		}
	}

	public function delete(): void
	{
		$tax_jurisdictions_to_delete = $this->request->getPost('ids');

		if($this->tax_jurisdiction->delete_list($tax_jurisdictions_to_delete))
		{
			echo json_encode ([
				'success' => TRUE,
				'message' => lang('Tax_jurisdictions.successful_deleted') . ' ' . count($tax_jurisdictions_to_delete) . ' ' . lang('Tax_jurisdictions.one_or_multiple')
			]);
		}
		else
		{
			echo json_encode (['success' => FALSE, 'message' => lang('Tax_jurisdictions.cannot_be_deleted')]);
		}
	}
}
?>