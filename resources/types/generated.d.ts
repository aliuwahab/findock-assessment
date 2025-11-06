declare namespace App.Data.Web {
export type CsvFieldData = {
id: number;
csv_upload_id: number;
field_data: Array<any>;
validation_status: string;
validation_result: Array<any> | null;
created_at: string;
updated_at: string;
};
export type CsvUploadData = {
id: number;
file_name: string;
uploaded_by: number;
status: string;
total_rows: number;
processed_rows: number;
progress_percentage: number;
processing_started_at: string | null;
processing_completed_at: string | null;
uploaded_at: string | null;
created_at: string;
updated_at: string;
};
}
declare namespace App.Domain.AddressValidation.ValueObjects {
export type ValidationStatus = 'pending' | 'processing' | 'valid' | 'invalid' | 'error';
}
