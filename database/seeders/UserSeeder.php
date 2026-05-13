<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder - Dữ liệu người dùng thực tế
 * Gồm: 1 Admin, 3 Quản lý (3 chi nhánh), 5 Nhân viên lễ tân, 6 PT, ~80 Hội viên, ~15 Khách vãng lai
 *
 * Role mapping:
 *   1 = admin   | 2 = manager | 3 = staff (lễ tân)
 *   4 = trainer | 5 = member  | 6 = guest (khách vãng lai)
 *
 * Branch mapping:
 *   1 = Quận 1 | 2 = Bình Thạnh | 3 = Gò Vấp
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('vi_VN');

        // =====================================================================
        // 1. ADMIN (chỉ 1)
        // =====================================================================
        User::firstOrCreate(['email' => 'admin@fitlifegym.vn'], [
            'name'        => 'admin',
            'full_name'   => 'Nguyễn Minh Khoa',
            'password'    => Hash::make('password'),
            'role_id'     => 1,
            'branch_id'   => 1,
            'phone'       => '0901000001',
            'gender'      => 'male',
            'card_number' => 'ADM-0001',
            'e_number'    => 'E-ADM001',
            'state'       => 'active',
        ]);

        // =====================================================================
        // 2. QUẢN LÝ (1 quản lý / chi nhánh)
        // =====================================================================
        $managers = [
            [
                'name' => 'manager', 'full_name' => 'Trần Thị Hương', 'email' => 'manager.q1@fitlifegym.vn',
                'phone' => '0902100001', 'gender' => 'female', 'branch_id' => 1,
                'card_number' => 'MGR-B1-001', 'e_number' => 'E-MGR001',
            ],
            [
                'name' => 'manager', 'full_name' => 'Lê Quang Vinh', 'email' => 'manager.bt@fitlifegym.vn',
                'phone' => '0902100002', 'gender' => 'male', 'branch_id' => 2,
                'card_number' => 'MGR-B2-001', 'e_number' => 'E-MGR002',
            ],
            [
                'name' => 'manager', 'full_name' => 'Phạm Ngọc Ánh', 'email' => 'manager.gv@fitlifegym.vn',
                'phone' => '0902100003', 'gender' => 'female', 'branch_id' => 3,
                'card_number' => 'MGR-B3-001', 'e_number' => 'E-MGR003',
            ],
        ];
        foreach ($managers as $m) {
            User::firstOrCreate(['email' => $m['email']], array_merge($m, [
                'password' => Hash::make('password'),
                'role_id'  => 2,
                'state'    => 'active',
            ]));
        }

        // =====================================================================
        // 3. NHÂN VIÊN LỄ TÂN (5 người, phân bổ 3 chi nhánh)
        // =====================================================================
        $staffs = [
            ['name' => 'staff', 'full_name' => 'Nguyễn Thị Mai',    'email' => 'staff.mai@fitlifegym.vn',   'phone' => '0903200001', 'gender' => 'female', 'branch_id' => 1, 'card_number' => 'STF-B1-001', 'e_number' => 'E-STF001'],
            ['name' => 'staff', 'full_name' => 'Võ Thanh Đức',      'email' => 'staff.duc@fitlifegym.vn',   'phone' => '0903200002', 'gender' => 'male',   'branch_id' => 1, 'card_number' => 'STF-B1-002', 'e_number' => 'E-STF002'],
            ['name' => 'staff', 'full_name' => 'Đặng Thùy Linh',    'email' => 'staff.linh@fitlifegym.vn',  'phone' => '0903200003', 'gender' => 'female', 'branch_id' => 2, 'card_number' => 'STF-B2-001', 'e_number' => 'E-STF003'],
            ['name' => 'staff', 'full_name' => 'Bùi Văn Tiến',      'email' => 'staff.tien@fitlifegym.vn',  'phone' => '0903200004', 'gender' => 'male',   'branch_id' => 2, 'card_number' => 'STF-B2-002', 'e_number' => 'E-STF004'],
            ['name' => 'staff', 'full_name' => 'Hoàng Minh Phương', 'email' => 'staff.phuong@fitlifegym.vn','phone' => '0903200005', 'gender' => 'female', 'branch_id' => 3, 'card_number' => 'STF-B3-001', 'e_number' => 'E-STF005'],
        ];
        foreach ($staffs as $s) {
            User::firstOrCreate(['email' => $s['email']], array_merge($s, [
                'password' => Hash::make('password'),
                'role_id'  => 3,
                'state'    => 'active',
            ]));
        }

        // =====================================================================
        // 4. HUẤN LUYỆN VIÊN PT (6 người)
        // =====================================================================
        $trainers = [
            ['name' => 'pt', 'full_name' => 'Lê Văn Minh',     'email' => 'pt.minh@fitlifegym.vn',   'phone' => '0904300001', 'gender' => 'male',   'branch_id' => 1, 'card_number' => 'PT-B1-001', 'e_number' => 'E-PT001'],
            ['name' => 'pt', 'full_name' => 'Nguyễn Thị Hoa',  'email' => 'pt.hoa@fitlifegym.vn',    'phone' => '0904300002', 'gender' => 'female', 'branch_id' => 1, 'card_number' => 'PT-B1-002', 'e_number' => 'E-PT002'],
            ['name' => 'pt', 'full_name' => 'Phạm Anh Tuấn',   'email' => 'pt.tuan@fitlifegym.vn',   'phone' => '0904300003', 'gender' => 'male',   'branch_id' => 2, 'card_number' => 'PT-B2-001', 'e_number' => 'E-PT003'],
            ['name' => 'pt', 'full_name' => 'Trần Thị Lan',     'email' => 'pt.lan@fitlifegym.vn',    'phone' => '0904300004', 'gender' => 'female', 'branch_id' => 2, 'card_number' => 'PT-B2-002', 'e_number' => 'E-PT004'],
            ['name' => 'pt', 'full_name' => 'Võ Quốc Hùng',    'email' => 'pt.hung@fitlifegym.vn',   'phone' => '0904300005', 'gender' => 'male',   'branch_id' => 3, 'card_number' => 'PT-B3-001', 'e_number' => 'E-PT005'],
            ['name' => 'pt', 'full_name' => 'Đinh Thị Thảo',   'email' => 'pt.thao@fitlifegym.vn',   'phone' => '0904300006', 'gender' => 'female', 'branch_id' => 3, 'card_number' => 'PT-B3-002', 'e_number' => 'E-PT006'],
        ];
        foreach ($trainers as $t) {
            User::firstOrCreate(['email' => $t['email']], array_merge($t, [
                'password' => Hash::make('password'),
                'role_id'  => 4,
                'state'    => 'active',
            ]));
        }

        // =====================================================================
        // 5. HỘI VIÊN (members) - 80 người thực tế
        // Phân bổ: 40 người B1, 25 người B2, 15 người B3
        // Trạng thái mix: active 70%, inactive 15%, banned 5%, expired 10%
        // =====================================================================
        $memberData = [
            // Quận 1 - 40 hội viên
            ['B1', 'Nguyễn Văn An',       'member.an@gmail.com',        '0905100001', 'male',   '1998-04-15'],
            ['B1', 'Trần Thị Bình',       'member.binh@gmail.com',      '0905100002', 'female', '1995-08-22'],
            ['B1', 'Lê Minh Châu',        'member.chau@gmail.com',      '0905100003', 'male',   '2001-11-10'],
            ['B1', 'Phạm Thu Dung',       'member.dung@gmail.com',      '0905100004', 'female', '1999-03-05'],
            ['B1', 'Hoàng Văn Em',        'member.em@gmail.com',        '0905100005', 'male',   '1997-07-18'],
            ['B1', 'Vũ Thị Phương',       'member.phuong@gmail.com',    '0905100006', 'female', '2000-12-30'],
            ['B1', 'Đặng Quốc Giang',     'member.giang@gmail.com',     '0905100007', 'male',   '1996-01-25'],
            ['B1', 'Bùi Thị Hạnh',        'member.hanh@gmail.com',      '0905100008', 'female', '2002-06-14'],
            ['B1', 'Đỗ Văn Hải',          'member.hai@gmail.com',       '0905100009', 'male',   '1994-09-08'],
            ['B1', 'Ngô Thị Huyền',       'member.huyen@gmail.com',     '0905100010', 'female', '1998-02-27'],
            ['B1', 'Lý Quang Khải',       'member.khai@gmail.com',      '0905100011', 'male',   '2000-05-03'],
            ['B1', 'Đinh Thị Lan',        'member.lan.q1@gmail.com',    '0905100012', 'female', '1997-10-19'],
            ['B1', 'Trương Văn Long',     'member.long@gmail.com',      '0905100013', 'male',   '1995-04-12'],
            ['B1', 'Phan Thị Mai',        'member.mai.q1@gmail.com',    '0905100014', 'female', '2001-08-07'],
            ['B1', 'Tô Minh Nam',         'member.nam@gmail.com',       '0905100015', 'male',   '1993-12-20'],
            ['B1', 'Cao Thị Ngọc',        'member.ngoc.q1@gmail.com',   '0905100016', 'female', '1999-07-11'],
            ['B1', 'Lâm Văn Phú',         'member.phu@gmail.com',       '0905100017', 'male',   '2003-03-28'],
            ['B1', 'Huỳnh Thị Quỳnh',    'member.quynh@gmail.com',     '0905100018', 'female', '1998-11-16'],
            ['B1', 'Nguyễn Duy Sơn',     'member.son@gmail.com',       '0905100019', 'male',   '1996-06-04'],
            ['B1', 'Võ Thị Thanh',        'member.thanh.q1@gmail.com',  '0905100020', 'female', '2000-01-22'],
            ['B1', 'Trần Quốc Thắng',    'member.thang@gmail.com',     '0905100021', 'male',   '1994-08-30'],
            ['B1', 'Lê Thị Thu',          'member.thu.q1@gmail.com',    '0905100022', 'female', '2002-04-17'],
            ['B1', 'Phạm Văn Tiến',       'member.tien@gmail.com',      '0905100023', 'male',   '1997-12-08'],
            ['B1', 'Hoàng Thị Trang',    'member.trang.q1@gmail.com',  '0905100024', 'female', '1999-05-25'],
            ['B1', 'Vũ Minh Tuấn',        'member.tuan.q1@gmail.com',   '0905100025', 'male',   '1995-10-14'],
            ['B1', 'Đặng Thị Uyên',       'member.uyen@gmail.com',      '0905100026', 'female', '2001-02-09'],
            ['B1', 'Bùi Quốc Việt',       'member.viet@gmail.com',      '0905100027', 'male',   '1998-09-03'],
            ['B1', 'Đỗ Thị Xuân',         'member.xuan@gmail.com',      '0905100028', 'female', '2000-07-21'],
            ['B1', 'Ngô Văn Yên',         'member.yen@gmail.com',       '0905100029', 'male',   '1996-03-15'],
            ['B1', 'Lý Thị Zung',         'member.zung@gmail.com',      '0905100030', 'female', '2003-01-06'],
            ['B1', 'Đinh Công Anh',       'member.canh@gmail.com',      '0905100031', 'male',   '1993-11-28'],
            ['B1', 'Trương Thị Bảo',     'member.bao@gmail.com',       '0905100032', 'female', '1997-06-18'],
            ['B1', 'Phan Văn Cường',      'member.cuong@gmail.com',     '0905100033', 'male',   '2000-09-10'],
            ['B1', 'Tô Thị Diệu',         'member.dieu@gmail.com',      '0905100034', 'female', '1998-04-29'],
            ['B1', 'Cao Văn Đạt',         'member.dat@gmail.com',       '0905100035', 'male',   '1995-12-07'],
            ['B1', 'Lâm Thị Giang',       'member.giang.f@gmail.com',   '0905100036', 'female', '2001-07-23'],
            ['B1', 'Huỳnh Văn Hiếu',     'member.hieu@gmail.com',      '0905100037', 'male',   '1999-02-12'],
            ['B1', 'Nguyễn Thị Hiền',    'member.hien@gmail.com',      '0905100038', 'female', '1996-10-05'],
            ['B1', 'Võ Văn Khang',        'member.khang@gmail.com',     '0905100039', 'male',   '2002-05-30'],
            ['B1', 'Trần Thị Kim',        'member.kim@gmail.com',       '0905100040', 'female', '1994-08-19'],
            // Bình Thạnh - 25 hội viên
            ['B2', 'Lê Văn Bảo',          'member.bao.bt@gmail.com',   '0905200001', 'male',   '1998-03-11'],
            ['B2', 'Phạm Thị Cẩm',        'member.cam@gmail.com',      '0905200002', 'female', '2001-07-04'],
            ['B2', 'Hoàng Minh Dũng',     'member.dung.bt@gmail.com',  '0905200003', 'male',   '1996-11-22'],
            ['B2', 'Vũ Thị Diễm',         'member.diem@gmail.com',     '0905200004', 'female', '2000-01-15'],
            ['B2', 'Đặng Văn Hậu',        'member.hau@gmail.com',      '0905200005', 'male',   '1995-06-29'],
            ['B2', 'Bùi Thị Hương',       'member.huong@gmail.com',    '0905200006', 'female', '1999-10-08'],
            ['B2', 'Đỗ Quốc Khánh',       'member.khanh@gmail.com',    '0905200007', 'male',   '2002-04-17'],
            ['B2', 'Ngô Thị Loan',        'member.loan@gmail.com',     '0905200008', 'female', '1997-09-26'],
            ['B2', 'Lý Văn Mạnh',         'member.manh@gmail.com',     '0905200009', 'male',   '1993-12-13'],
            ['B2', 'Đinh Thị Ngân',       'member.ngan@gmail.com',     '0905200010', 'female', '2001-05-07'],
            ['B2', 'Trương Văn Phong',    'member.phong@gmail.com',    '0905200011', 'male',   '1998-08-24'],
            ['B2', 'Phan Thị Phúc',       'member.phuc@gmail.com',     '0905200012', 'female', '1996-02-19'],
            ['B2', 'Tô Văn Quân',         'member.quan@gmail.com',     '0905200013', 'male',   '2000-11-03'],
            ['B2', 'Cao Thị Râm',         'member.ram@gmail.com',      '0905200014', 'female', '1999-07-16'],
            ['B2', 'Lâm Văn Sang',        'member.sang@gmail.com',     '0905200015', 'male',   '1994-04-05'],
            ['B2', 'Huỳnh Thị Sen',       'member.sen@gmail.com',      '0905200016', 'female', '2003-01-28'],
            ['B2', 'Nguyễn Văn Tâm',     'member.tam@gmail.com',      '0905200017', 'male',   '1997-09-12'],
            ['B2', 'Võ Thị Thơ',          'member.tho@gmail.com',      '0905200018', 'female', '2001-03-22'],
            ['B2', 'Trần Minh Toàn',      'member.toan@gmail.com',     '0905200019', 'male',   '1995-07-08'],
            ['B2', 'Lê Thị Trúc',         'member.truc@gmail.com',     '0905200020', 'female', '2000-12-01'],
            ['B2', 'Phạm Văn Tú',         'member.tu@gmail.com',       '0905200021', 'male',   '1998-05-19'],
            ['B2', 'Hoàng Thị Vân',       'member.van.bt@gmail.com',   '0905200022', 'female', '1996-10-27'],
            ['B2', 'Vũ Văn Xuân',         'member.xuan.bt@gmail.com',  '0905200023', 'male',   '2002-02-14'],
            ['B2', 'Đặng Thị Yến',        'member.yen.bt@gmail.com',   '0905200024', 'female', '1999-08-06'],
            ['B2', 'Bùi Minh Châu',       'member.chau.bt@gmail.com',  '0905200025', 'male',   '1993-06-30'],
            // Gò Vấp - 15 hội viên
            ['B3', 'Đỗ Thị Ân',           'member.an.gv@gmail.com',    '0905300001', 'female', '2000-04-18'],
            ['B3', 'Ngô Văn Bình',        'member.binh.gv@gmail.com',  '0905300002', 'male',   '1997-09-05'],
            ['B3', 'Lý Thị Cúc',          'member.cuc@gmail.com',      '0905300003', 'female', '2002-01-23'],
            ['B3', 'Đinh Văn Đông',       'member.dong@gmail.com',     '0905300004', 'male',   '1994-07-11'],
            ['B3', 'Trương Thị Hà',       'member.ha.gv@gmail.com',    '0905300005', 'female', '1999-12-29'],
            ['B3', 'Phan Quốc Huy',       'member.huy.gv@gmail.com',   '0905300006', 'male',   '2001-06-16'],
            ['B3', 'Tô Thị Hằng',         'member.hang.gv@gmail.com',  '0905300007', 'female', '1996-03-08'],
            ['B3', 'Cao Văn Lâm',         'member.lam.gv@gmail.com',   '0905300008', 'male',   '1998-10-25'],
            ['B3', 'Lâm Thị Nhi',         'member.nhi@gmail.com',      '0905300009', 'female', '2003-05-13'],
            ['B3', 'Huỳnh Văn Phát',     'member.phat@gmail.com',     '0905300010', 'male',   '1995-01-31'],
            ['B3', 'Nguyễn Thị Quyên',   'member.quyen@gmail.com',    '0905300011', 'female', '2000-08-20'],
            ['B3', 'Võ Văn Rũng',         'member.rung@gmail.com',     '0905300012', 'male',   '1997-04-09'],
            ['B3', 'Trần Thị Sương',      'member.suong@gmail.com',    '0905300013', 'female', '2001-11-17'],
            ['B3', 'Lê Văn Tài',          'member.tai@gmail.com',      '0905300014', 'male',   '1993-09-03'],
            ['B3', 'Phạm Thị Uyên',       'member.uyen.gv@gmail.com',  '0905300015', 'female', '1998-07-14'],
        ];

        $branchMap = ['B1' => 1, 'B2' => 2, 'B3' => 3];
        $i = 1;
        foreach ($memberData as [$branch, $fullName, $email, $phone, $gender, $dob]) {
            $branchId   = $branchMap[$branch];
            $prefix     = strtoupper($branch);
            $cardNumber = "MBR-{$prefix}-" . str_pad($i, 3, '0', STR_PAD_LEFT);
            // 70% active, 15% inactive, 10% expired (handled by subscription), 5% banned
            $state = match (true) {
                $i % 20 === 0 => 'banned',
                $i % 7 === 0  => 'inactive',
                default       => 'active',
            };
            User::firstOrCreate(['email' => $email], [
                'name'        => 'hội viên',
                'full_name'   => $fullName,
                'password'    => Hash::make('password'),
                'role_id'     => 5,
                'branch_id'   => $branchId,
                'phone'       => $phone,
                'gender'      => $gender,
                'card_number' => $cardNumber,
                'state'       => $state,
            ]);
            $i++;
        }

        // =====================================================================
        // 6. KHÁCH VÃNG LAI (guests) - 15 người
        // Khách vãng lai không nhất thiết có gói tập dài hạn,
        // thường chỉ ghé check-in 1 lần hoặc mua gói ngắn.
        // =====================================================================
        $guests = [
            ['Phan Văn Chính',      'guest.chinh@gmail.com',   '0906100001', 'male',   1],
            ['Tô Thị Duyên',        'guest.duyen@gmail.com',   '0906100002', 'female', 1],
            ['Cao Văn Hảo',         'guest.hao@gmail.com',     '0906100003', 'male',   2],
            ['Lâm Thị Kiều',        'guest.kieu@gmail.com',    '0906100004', 'female', 2],
            ['Huỳnh Văn Lộc',       'guest.loc@gmail.com',     '0906100005', 'male',   1],
            ['Nguyễn Thị Mơ',       'guest.mo@gmail.com',      '0906100006', 'female', 3],
            ['Võ Văn Nghĩa',        'guest.nghia@gmail.com',   '0906100007', 'male',   3],
            ['Trần Thị Oanh',       'guest.oanh@gmail.com',    '0906100008', 'female', 1],
            ['Lê Văn Phú',          'guest.phu@gmail.com',     '0906100009', 'male',   2],
            ['Phạm Thị Quê',        'guest.que@gmail.com',     '0906100010', 'female', 1],
            ['Hoàng Văn Rạng',      'guest.rang@gmail.com',    '0906100011', 'male',   3],
            ['Vũ Thị Sim',          'guest.sim@gmail.com',     '0906100012', 'female', 2],
            ['Đặng Văn Tâm',        'guest.tam@gmail.com',     '0906100013', 'male',   1],
            ['Bùi Thị Uyên',        'guest.uyen@gmail.com',    '0906100014', 'female', 1],
            ['Đỗ Văn Vọng',         'guest.vong@gmail.com',    '0906100015', 'male',   2],
        ];
        $gi = 1;
        foreach ($guests as [$fullName, $email, $phone, $gender, $branchId]) {
            User::firstOrCreate(['email' => $email], [
                'name'        => 'khách vãng lai',
                'full_name'   => $fullName,
                'password'    => Hash::make('password'),
                'role_id'     => 6,
                'branch_id'   => $branchId,
                'phone'       => $phone,
                'gender'      => $gender,
                'card_number' => 'GST-' . str_pad($gi, 3, '0', STR_PAD_LEFT),
                'state'       => 'active',
            ]);
            $gi++;
        }
    }
}
