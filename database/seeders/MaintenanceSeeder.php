<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Maintenance Center
        DB::table('maintenance_center')->insert([
            ['mc_reference' => 'MC2026-00001', 'mc_name' => 'Office of the President', 'mc_head_uaccid' => '9', 'mc_head_position' => 'President', 'mc_status' => 'Active'],
            ['mc_reference' => 'MC2026-00002', 'mc_name' => 'College for Academic Affairs', 'mc_head_uaccid' => '20', 'mc_head_position' => 'Dean / Vice President', 'mc_status' => 'Active'],
            ['mc_reference' => 'MC2026-00003', 'mc_name' => 'Center for Cadet Affairs', 'mc_head_uaccid' => '33', 'mc_head_position' => 'Vice-President, Cadet Affairs', 'mc_status' => 'Active'],
            ['mc_reference' => 'MC2026-00004', 'mc_name' => 'Center for Research and Extension Programs', 'mc_head_uaccid' => '12', 'mc_head_position' => 'Vice President', 'mc_status' => 'Active'],
            ['mc_reference' => 'MC2026-00005', 'mc_name' => 'Support Center', 'mc_head_uaccid' => '25', 'mc_head_position' => 'General Manager', 'mc_status' => 'Active'],
            ['mc_reference' => 'MC2026-00006', 'mc_name' => 'Finance Center', 'mc_head_uaccid' => '78', 'mc_head_position' => 'Chief Accountant', 'mc_status' => 'Active'],
            ['mc_reference' => 'MC2026-00007', 'mc_name' => 'Legal Counsel', 'mc_head_uaccid' => '0', 'mc_head_position' => '', 'mc_status' => 'Active'],
            ['mc_reference' => 'MC2026-00008', 'mc_name' => 'NTMA Student', 'mc_head_uaccid' => '33', 'mc_head_position' => 'Vice-President, Cadet Affairs', 'mc_status' => 'Active'],
        ]);

        // 2. Seed Maintenance Department
        DB::table('maintenance_department')->insert([
            ['md_reference' => 'MD2026-00001', 'md_center_reference' => 'MC2026-00005', 'md_name' => 'Network Operation Center', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00002', 'md_center_reference' => 'MC2026-00005', 'md_name' => 'General Services', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00003', 'md_center_reference' => 'MC2026-00005', 'md_name' => 'Human Resource', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00004', 'md_center_reference' => 'MC2026-00005', 'md_name' => 'Purchasing', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00005', 'md_center_reference' => 'MC2026-00002', 'md_name' => 'Department of Marine Transportation', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00006', 'md_center_reference' => 'MC2026-00002', 'md_name' => 'Department of Marine Engineering', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00007', 'md_center_reference' => 'MC2026-00002', 'md_name' => 'Department of General Education', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00008', 'md_center_reference' => 'MC2026-00002', 'md_name' => 'Nihongo and Kumon Learning', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00009', 'md_center_reference' => 'MC2026-00002', 'md_name' => 'Knowledge Resources Center', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00010', 'md_center_reference' => 'MC2026-00003', 'md_name' => 'Onboard Training Office', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00011', 'md_center_reference' => 'MC2026-00003', 'md_name' => 'Guidance and Counselling Office (Safe Harbor Hub)', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00012', 'md_center_reference' => 'MC2026-00003', 'md_name' => 'Cadet Support Services', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00013', 'md_center_reference' => 'MC2026-00003', 'md_name' => 'Cadet Coordinating Office', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00014', 'md_center_reference' => 'MC2026-00004', 'md_name' => 'CREP', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00015', 'md_center_reference' => 'MC2026-00001', 'md_name' => 'Transformative Leaders Unit', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00016', 'md_center_reference' => 'MC2026-00001', 'md_name' => 'Quality Standards Office', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00017', 'md_center_reference' => 'MC2026-00006', 'md_name' => 'Finance', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00018', 'md_center_reference' => 'MC2026-00006', 'md_name' => 'Finance (SLAF)', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00019', 'md_center_reference' => 'MC2026-00001', 'md_name' => 'Office of the Registrar', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00020', 'md_center_reference' => 'MC2026-00008', 'md_name' => 'Student council', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00021', 'md_center_reference' => 'MC2026-00001', 'md_name' => 'Office of the President', 'md_status' => 'Active'],
            ['md_reference' => 'MD2026-00022', 'md_center_reference' => 'MC2026-00005', 'md_name' => 'Department head', 'md_status' => 'Active'],
        ]);

        // 3. Seed Maintenance Position
        DB::table('maintenance_position')->insert([
            ['mp_reference' => 'MP2026-00001', 'mp_department_reference' => 'MD2026-00001', 'mp_name' => 'IT Supervisor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00002', 'mp_department_reference' => 'MD2026-00001', 'mp_name' => 'Senior IT Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00003', 'mp_department_reference' => 'MD2026-00001', 'mp_name' => 'IT Programmer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00004', 'mp_department_reference' => 'MD2026-00001', 'mp_name' => 'Cybersecurity Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00005', 'mp_department_reference' => 'MD2026-00002', 'mp_name' => 'Assistant Manager', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00006', 'mp_department_reference' => 'MD2026-00002', 'mp_name' => 'General Services Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00007', 'mp_department_reference' => 'MD2026-00002', 'mp_name' => 'Messenger', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00008', 'mp_department_reference' => 'MD2026-00002', 'mp_name' => 'Maintenance Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00009', 'mp_department_reference' => 'MD2026-00003', 'mp_name' => 'Human Resource Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00010', 'mp_department_reference' => 'MD2026-00003', 'mp_name' => 'Human Resource Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00011', 'mp_department_reference' => 'MD2026-00004', 'mp_name' => 'Purchasing Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00012', 'mp_department_reference' => 'MD2026-00004', 'mp_name' => 'Purchasing Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00013', 'mp_department_reference' => 'MD2026-00005', 'mp_name' => 'Department Head', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00014', 'mp_department_reference' => 'MD2026-00005', 'mp_name' => 'Instructor 1', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00015', 'mp_department_reference' => 'MD2026-00005', 'mp_name' => 'Deck Laboratory Technician', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00016', 'mp_department_reference' => 'MD2026-00006', 'mp_name' => 'Department Head', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00017', 'mp_department_reference' => 'MD2026-00006', 'mp_name' => 'Instructor 1', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00018', 'mp_department_reference' => 'MD2026-00006', 'mp_name' => 'Engine Laboratory Technician', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00019', 'mp_department_reference' => 'MD2026-00006', 'mp_name' => 'Staff CAA', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00020', 'mp_department_reference' => 'MD2026-00007', 'mp_name' => 'Department Head', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00021', 'mp_department_reference' => 'MD2026-00007', 'mp_name' => 'Assistant Instructor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00022', 'mp_department_reference' => 'MD2026-00007', 'mp_name' => 'NSTP Instructor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00023', 'mp_department_reference' => 'MD2026-00007', 'mp_name' => 'Associate Professor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00024', 'mp_department_reference' => 'MD2026-00007', 'mp_name' => 'ICT Instructor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00025', 'mp_department_reference' => 'MD2026-00007', 'mp_name' => 'Mathematics Instructor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00026', 'mp_department_reference' => 'MD2026-00008', 'mp_name' => 'Nihongo Instructor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00027', 'mp_department_reference' => 'MD2026-00009', 'mp_name' => 'Chief Librarian', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00028', 'mp_department_reference' => 'MD2026-00009', 'mp_name' => 'Junior Librarian', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00029', 'mp_department_reference' => 'MD2026-00009', 'mp_name' => 'Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00030', 'mp_department_reference' => 'MD2026-00010', 'mp_name' => 'Onboard Training Supervisor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00031', 'mp_department_reference' => 'MD2026-00010', 'mp_name' => 'Onboard Training Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00032', 'mp_department_reference' => 'MD2026-00010', 'mp_name' => 'Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00033', 'mp_department_reference' => 'MD2026-00011', 'mp_name' => 'Guidance Counselor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00034', 'mp_department_reference' => 'MD2026-00011', 'mp_name' => 'Junior Guidance Associate', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00035', 'mp_department_reference' => 'MD2026-00012', 'mp_name' => 'Cadet Support Services Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00036', 'mp_department_reference' => 'MD2026-00012', 'mp_name' => 'Nurse ( Men )', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00037', 'mp_department_reference' => 'MD2026-00012', 'mp_name' => 'Nurse ( Women )', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00038', 'mp_department_reference' => 'MD2026-00012', 'mp_name' => 'Cadet Support Services Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00039', 'mp_department_reference' => 'MD2026-00012', 'mp_name' => 'Chef', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00040', 'mp_department_reference' => 'MD2026-00012', 'mp_name' => 'Assistant Cook / Slop Chest / Cafetorium Cashier', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00041', 'mp_department_reference' => 'MD2026-00013', 'mp_name' => 'Cadet Coordinating Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00042', 'mp_department_reference' => 'MD2026-00013', 'mp_name' => 'Cadet Coordinating Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00043', 'mp_department_reference' => 'MD2026-00013', 'mp_name' => 'Physical Fitness Coordinator', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00044', 'mp_department_reference' => 'MD2026-00014', 'mp_name' => 'Research and Extension Coordinator', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00045', 'mp_department_reference' => 'MD2026-00014', 'mp_name' => 'Technical Writer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00046', 'mp_department_reference' => 'MD2026-00014', 'mp_name' => 'Data Analyst', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00047', 'mp_department_reference' => 'MD2026-00015', 'mp_name' => 'Technical Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00048', 'mp_department_reference' => 'MD2026-00016', 'mp_name' => 'Assistant Manager, QSO', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00049', 'mp_department_reference' => 'MD2026-00016', 'mp_name' => 'Staff, QSO', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00050', 'mp_department_reference' => 'MD2026-00017', 'mp_name' => 'Chief Accountant', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00051', 'mp_department_reference' => 'MD2026-00017', 'mp_name' => 'Accounting Supervisor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00052', 'mp_department_reference' => 'MD2026-00017', 'mp_name' => 'Senior Accountant', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00053', 'mp_department_reference' => 'MD2026-00017', 'mp_name' => 'Accountant', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00054', 'mp_department_reference' => 'MD2026-00017', 'mp_name' => 'Cashier', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00055', 'mp_department_reference' => 'MD2026-00018', 'mp_name' => 'Admin Supervisor, SLAF', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00056', 'mp_department_reference' => 'MD2026-00018', 'mp_name' => 'Staff, SLAF', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00057', 'mp_department_reference' => 'MD2026-00019', 'mp_name' => 'Registrar', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00058', 'mp_department_reference' => 'MD2026-00019', 'mp_name' => 'Admission Officer', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00059', 'mp_department_reference' => 'MD2026-00019', 'mp_name' => 'Staff', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00060', 'mp_department_reference' => 'MD2026-00007', 'mp_name' => 'Assistant Instructor / NSTP Instructor', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00061', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Executive Committee', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00062', 'mp_department_reference' => 'MD2026-00021', 'mp_name' => 'President', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00063', 'mp_department_reference' => 'MD2026-00022', 'mp_name' => 'General Manager', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00064', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Training Team', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00065', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Honor Committee', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00066', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Blue Horizon', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00067', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Creative Waves', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00068', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Aquatic Alchemist', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00069', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Math Club', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00070', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'NTMA Christian Fellowship', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00071', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Peer Facilitators Circle', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00072', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => "Mariners' Club", 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00073', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Sports Club', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00074', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Medical Team', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00075', 'mp_department_reference' => 'MD2026-00020', 'mp_name' => 'Nihongo Club', 'mp_status' => 'Active'],
            ['mp_reference' => 'MP2026-00076', 'mp_department_reference' => 'MD2026-00021', 'mp_name' => 'Vice President', 'mp_status' => 'Active'],
        ]);
    }
}