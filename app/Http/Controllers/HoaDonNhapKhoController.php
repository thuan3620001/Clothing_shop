<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteChiTietNhapKhoRequest;
use App\Http\Requests\UpdateChiTietNhapKhoRequest;
use App\Models\ChiTietHoaDonNhapKho;
use App\Models\HoaDonNhapKho;
use App\Models\NhaCungCap;
use Illuminate\Http\Request;

class HoaDonNhapKhoController extends Controller
{
    public function index($id_nha_cung_cap)
    {
        // $nhaCungCap = NhaCungCap::where('id', $id_nha_cung_cap)->first();
        $nhaCungCap = NhaCungCap::find($id_nha_cung_cap);
        if($nhaCungCap) {
            $hoaDonNhapKho = HoaDonNhapKho::where('id_nha_cung_cap', $id_nha_cung_cap)
                                          ->where('tinh_trang', 0) // Đang nhập liệu
                                          ->first();
            if(!$hoaDonNhapKho) {
                $hoaDonNhapKho = HoaDonNhapKho::create([
                    'id_nha_cung_cap'   => $id_nha_cung_cap
                ]);
            }
            $id_hoa_don = $hoaDonNhapKho->id;

            return view('admin.page.nhap_kho.index', compact('hoaDonNhapKho', 'id_hoa_don'));
        } else {
            toastr()->error('Nhà cung cấp không tồn tại.', "Error!");
            return redirect('/admin/nha-cung-cap/index');
        }
    }

    public function data($id_hoa_don_nhap_kho)
    {
        $data = ChiTietHoaDonNhapKho::where('id_hoa_don_nhap', $id_hoa_don_nhap_kho)->get();

        return response()->json([
            'status'    => true,
            'data'      => $data,
        ]);
    }

    public function store(Request $request)
    {
        $chiTietHoaDon = ChiTietHoaDonNhapKho::where('id_hoa_don_nhap', $request->id_hoa_don_nhap)
                                             ->where('id_san_pham', $request->id_san_pham)
                                             ->first();
        if($chiTietHoaDon) {
            $chiTietHoaDon->so_luong_nhap = $chiTietHoaDon->so_luong_nhap +  1;
            $chiTietHoaDon->save();
        } else {
            $chiTietHoaDon = ChiTietHoaDonNhapKho::create([
                'id_hoa_don_nhap'   => $request->id_hoa_don_nhap,
                'id_san_pham'       => $request->id_san_pham,
                'ten_san_pham'      => $request->ten_san_pham,
            ]);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Đã nhập kho!',
        ]);
    }

    public function update(UpdateChiTietNhapKhoRequest $request)
    {
        $chiTiet = ChiTietHoaDonNhapKho::find($request->id);
        $chiTiet->update($request->all());

        return response()->json([
            'status'    => true,
            'message'   => 'Đã cập nhật chi tiết nhập kho!',
        ]);
    }

    public function destroy(DeleteChiTietNhapKhoRequest $request)
    {
        $chiTiet = ChiTietHoaDonNhapKho::find($request->id);
        $hoaDon  = HoaDonNhapKho::find($chiTiet->id_hoa_don_nhap);

        if($hoaDon && $hoaDon->tinh_trang == 0) {
            $chiTiet->delete();

            return response()->json([
                'status'    => true,
                'message'   => 'Đã xóa hóa đơn thành công!',
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Bạn không thể xóa!',
            ]);
        }
    }
}
