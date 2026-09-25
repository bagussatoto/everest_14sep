<?php


class ToolCek extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->masterConfigUi = $this->config->item("heTransaksi_ui");
        $this->load->helper("he_angka");
    }

    function index()
    {
        $arrTools = array(
            "kas" => "viewUnsyncedKas",
            "produk" => "viewUnsyncedProduk",
            "produk rakitan" => "viewUnsyncedProdukRakitan",
            "supplies" => "viewUnsyncedSupplies",
            "valas" => "viewUnsyncedValas",
            "audit_project_3way" => "cekProjectTps3Way",
            "edit_registry_project" => "editRegistryProject",
        );

//        foreach ($arrTools as $key => $value) {
//            echo "<div>";
//            echo "<h3>";
//            echo "<a href='" . base_url() . get_class($this) . "/$value' target='_blank'>:: $key ::</a>";
//            echo "</h3>";
//            echo "</div>";
//        }
    }

    function cekMasterDetail()
    {
        $tbl_master = "__rek_master__1010030030";
        $tbl_detail = "__rek_pembantu_produk__1010030030";

//        $cabang_id = "-1";
        $cabang_id = "1";
        $tahun = "2024";
//        $tahun = "2025";


        $where = array(
            "cabang_id" => $cabang_id,
            "year(dtime)" => $tahun,
        );
        //---------------------------------
        $this->db->where($where);
        $queryMaster = $this->db->get($tbl_master)->result();
//        showLast_query("biru");
//        cekBiru(count($query));
        //---------------------------------

        $this->db->where($where);
        $queryDetail = $this->db->get($tbl_detail)->result();
        showLast_query("kuning");
//        cekKuning(count($query));
        //---------------------------------

        $detailData = array();
        foreach ($queryDetail as $spec) {
            $trid = $spec->transaksi_id;
            if (!isset($detailData[$trid]["debet"])) {
                $detailData[$trid]["debet"] = 0;
            }
            if (!isset($detailData[$trid]["kredit"])) {
                $detailData[$trid]["kredit"] = 0;
            }
            $detailData[$trid]["debet"] += $spec->debet;
            $detailData[$trid]["kredit"] += $spec->kredit;

//            break;
        }
//arrPrint($detailData);
//mati_disini();
        $masterData = array();
        foreach ($queryMaster as $spec) {
            $trid = $spec->transaksi_id;
            $trno = $spec->transaksi_no;
            $debet = $spec->debet;
            $kredit = $spec->kredit;
            $dtime = $spec->dtime;
            $fulldate = $spec->fulldate;
            $debet_detail = isset($detailData[$trid]["debet"]) ? $detailData[$trid]["debet"] : 0;
            $kredit_detail = isset($detailData[$trid]["kredit"]) ? $detailData[$trid]["kredit"] : 0;

            $masterData[$trid]["trid"] = $trid;
            $masterData[$trid]["trno"] = $trno;
            $masterData[$trid]["debet_detail"] = $debet_detail;
            $masterData[$trid]["kredit_detail"] = $kredit_detail;
            $masterData[$trid]["dtime"] = $dtime;
            $masterData[$trid]["fulldate"] = $fulldate;
            if (!isset($masterData[$trid]["master_debet"])) {
                $masterData[$trid]["master_debet"] = 0;
            }
            if (!isset($masterData[$trid]["master_kredit"])) {
                $masterData[$trid]["master_kredit"] = 0;
            }
            $masterData[$trid]["master_debet"] += $debet;
            $masterData[$trid]["master_kredit"] += $kredit;
//            break;
        }
//        arrPrintCyan($masterData[372490]);

        $str = "<table rules='all' width='100%' style='border:1px solid black;'>";
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>No.</td>";
        $str .= "<td>tgl</td>";
        $str .= "<td>trid</td>";
        $str .= "<td>nomer</td>";
        $str .= "<td style='background-color:yellow;'>m debet</td>";
        $str .= "<td style='background-color:#5cb730;'>m kredit</td>";
        $str .= "<td style='background-color:yellow;'>d debet</td>";
        $str .= "<td style='background-color:#5cb730;'>d kredit</td>";
        $str .= "</tr>";
        $no = 0;
        $master_debet_total = 0;
        $master_kredit_total = 0;
        $debet_detail_total = 0;
        $kredit_detail_total = 0;
        foreach ($masterData as $trid => $tridspec) {
            $fulldate = $tridspec["fulldate"];
            $nomer = $tridspec["trno"];
            $master_debet = $tridspec["master_debet"];
            $master_kredit = $tridspec["master_kredit"];
            $debet_detail = $tridspec["debet_detail"];
            $kredit_detail = $tridspec["kredit_detail"];
            $selisih_debet_cek = $master_debet - $debet_detail;
            $selisih_debet_cek = ($selisih_debet_cek < 0) ? ($selisih_debet_cek * -1) : $selisih_debet_cek;
            $selisih_kredit_cek = $master_kredit - $kredit_detail;
            $selisih_kredit_cek = ($selisih_kredit_cek < 0) ? ($selisih_kredit_cek * -1) : $selisih_kredit_cek;
            if (($selisih_debet_cek > 100) || ($selisih_kredit_cek > 100)) {

                $master_debet_total += $master_debet;
                $master_kredit_total += $master_kredit;
                $debet_detail_total += $debet_detail;
                $kredit_detail_total += $kredit_detail;
                $master_debet_f = number_format($master_debet);
                $master_kredit_f = number_format($master_kredit);
                $debet_detail_f = number_format($debet_detail);
                $kredit_detail_f = number_format($kredit_detail);
                $no++;
                $str .= "<tr>";
                $str .= "<td>$no</td>";
                $str .= "<td>$fulldate</td>";
                $str .= "<td>$trid</td>";
                $str .= "<td>$nomer</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_f</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_f</td>";
                $str .= "</tr>";

            }
        }
        $selisih_debet = $master_debet_total - $debet_detail_total;
        $selisih_kredit = $master_kredit_total - $kredit_detail_total;
        $master_debet_total_f = number_format($master_debet_total);
        $master_kredit_total_f = number_format($master_kredit_total);
        $debet_detail_total_f = number_format($debet_detail_total);
        $kredit_detail_total_f = number_format($kredit_detail_total);
        $selisih_debet_f = number_format($selisih_debet);
        $selisih_kredit_f = number_format($selisih_kredit);
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_total_f</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_total_f</td>";
        $str .= "</tr>";
        $str .= "</table>";
        $str .= "<div >";
        $str .= "SELISIH DEBET: $selisih_debet_f";
        $str .= "<br>SELISIH KREDIT: $selisih_kredit_f";
        $str .= "</div>";
        echo $str;
    }


    public function cekCacheDobel()
    {
        $tbl_master_cache = "__rek_master__1010030030";
        $tbl_master = "__rek_master__1010030030";
        $tbl_detail = "__rek_pembantu_produk__1010030030";
        $tbl_detail_cache = "_rek_pembantu_produk_cache";
        $cabang_id = "1";
        $gudang_id = "-10";
        $periode = "forever";
        $arrDobel = array();
        $where = array(
            "cabang_id" => $cabang_id,
            "gudang_id" => $gudang_id,
            "periode" => $periode,
        );
        $this->db->where($where);
        $queryDetailCache = $this->db->get($tbl_detail_cache)->result();
        showLast_query("biru");

        foreach ($queryDetailCache as $spec) {

            $arrDobel[$spec->extern_id][$spec->id] = $spec->extern_id;
        }
        foreach ($arrDobel as $pid => $pspec) {
            if (sizeof($pspec) > 1) {
                arrPrint($pspec);
            }
        }


    }


    public function patchMasterValue()
    {
        $tbl_master_piutang = "__rek_master__1010060010";
        $tbl_master_hkp = "__rek_master__2040010";
        $tbl_master_hpp = "__rek_master__5010";
        $tbl_master = "__rek_master__1010030030";
//        $tbl_master = "__rek_master__2040010";
//        $tbl_master = "__rek_master__5010";
//        $tbl_master = "__rek_master__1010060010";
        $tbl_detail = "__rek_pembantu_produk__1010030030";

        $cabang_id = "-1";
//        $cabang_id = "1";
//        $tahun = "2024";
//        $tahun = "2025";


        $where = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
//            "transaksi_id" => 49290,
            "transaksi_id>" => 5,
            "gudang_id<>" => 0,
            "jenis" => "9911",
        );
        $where_master = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
        );
        //---------------------------------
        $this->db->where($where);
        $queryDetail = $this->db->get($tbl_detail)->result();
        showLast_query("kuning");
//        cekKuning(count($query));
//        mati_disini(__LINE__);
        //---------------------------------
        $detailData = array();
        $trIDs = array();
        foreach ($queryDetail as $spec) {
            $trid = $spec->transaksi_id;
            if ($trid > 0) {
                $trIDs[$trid] = $trid;
            }
            if (!isset($detailData[$trid]["debet"])) {
                $detailData[$trid]["debet"] = 0;
            }
            if (!isset($detailData[$trid]["kredit"])) {
                $detailData[$trid]["kredit"] = 0;
            }
            $detailData[$trid]["debet"] += $spec->debet;
            $detailData[$trid]["kredit"] += $spec->kredit;
        }

        $this->db->where($where_master);
        $this->db->where_in("transaksi_id", $trIDs);
        $queryMaster = $this->db->get($tbl_master)->result();
//        showLast_query("biru");
//        cekBiru(count($query));
        //---------------------------------


        $this->db->trans_start();


        $masterData = array();
        foreach ($queryMaster as $spec) {
            $trid = $spec->transaksi_id;
            $trno = $spec->transaksi_no;
            $debet = $spec->debet;
            $kredit = $spec->kredit;
            $dtime = $spec->dtime;
            $fulldate = $spec->fulldate;
            $debet_detail = isset($detailData[$trid]["debet"]) ? $detailData[$trid]["debet"] : 0;
            $kredit_detail = isset($detailData[$trid]["kredit"]) ? $detailData[$trid]["kredit"] : 0;

            $masterData[$trid]["trid"] = $trid;
            $masterData[$trid]["trno"] = $trno;
            $masterData[$trid]["debet_detail"] = $debet_detail;
            $masterData[$trid]["kredit_detail"] = $kredit_detail;
            $masterData[$trid]["dtime"] = $dtime;
            $masterData[$trid]["fulldate"] = $fulldate;
            if (!isset($masterData[$trid]["master_debet"])) {
                $masterData[$trid]["master_debet"] = 0;
            }
            if (!isset($masterData[$trid]["master_kredit"])) {
                $masterData[$trid]["master_kredit"] = 0;
            }
            $masterData[$trid]["master_debet"] += $debet;
            $masterData[$trid]["master_kredit"] += $kredit;
//            break;
        }
//        arrPrintCyan($masterData[372490]);

        $str = "<table rules='all' width='100%' style='border:1px solid black;'>";
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>No.</td>";
        $str .= "<td>tgl</td>";
        $str .= "<td>trid</td>";
        $str .= "<td>nomer</td>";
        $str .= "<td style='background-color:yellow;'>m debet</td>";
        $str .= "<td style='background-color:#5cb730;'>m kredit</td>";
        $str .= "<td style='background-color:yellow;'>d debet</td>";
        $str .= "<td style='background-color:#5cb730;'>d kredit</td>";
        $str .= "</tr>";
        $no = 0;
        $master_debet_total = 0;
        $master_kredit_total = 0;
        $debet_detail_total = 0;
        $kredit_detail_total = 0;
        foreach ($masterData as $trid => $tridspec) {
            $fulldate = $tridspec["fulldate"];
            $nomer = $tridspec["trno"];
            $master_debet = $tridspec["master_debet"];
            $master_kredit = $tridspec["master_kredit"];
            $debet_detail = $tridspec["debet_detail"];
            $kredit_detail = $tridspec["kredit_detail"];
            $netto_master = $master_debet - $master_kredit;
            $netto_detail = $debet_detail - $kredit_detail;
            // persediaan
            $selisih_debet_cek = $master_debet - $debet_detail;
            $selisih_kredit_cek = $debet_detail - $kredit_detail;
            $selisih_netto_cek = $netto_master - $netto_detail;
            // HPP
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // Hutang ke pusat
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // PIUTANG CABANG
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;

            $selisih_debet_cek = ($selisih_debet_cek < 0) ? ($selisih_debet_cek * -1) : $selisih_debet_cek;
            $selisih_kredit_cek = ($selisih_kredit_cek < 0) ? ($selisih_kredit_cek * -1) : $selisih_kredit_cek;
            $selisih_netto_cek = ($selisih_netto_cek < 0) ? ($selisih_netto_cek * -1) : $selisih_netto_cek;
//            cekUngu("[$selisih_debet_cek] [$selisih_kredit_cek]");
//            if (($selisih_debet_cek > 100) || ($selisih_kredit_cek > 100)) {
//            if ($selisih_netto_cek > 100) {
            $master_debet_total += $master_debet;
            $master_kredit_total += $master_kredit;
            $debet_detail_total += $debet_detail;
            $kredit_detail_total += $kredit_detail;
            $master_debet_f = number_format($master_debet);
            $master_kredit_f = number_format($master_kredit);
            $debet_detail_f = number_format($debet_detail);
            $kredit_detail_f = number_format($kredit_detail);
            $no++;
            $str .= "<tr>";
            $str .= "<td>$no</td>";
            $str .= "<td>$fulldate</td>";
            $str .= "<td>$trid</td>";
            $str .= "<td>$nomer</td>";
            $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_f</td>";
            $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_f</td>";
            $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_f</td>";
            $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_f</td>";
            $str .= "</tr>";

            //update tabel master persediaan----------------------------
            $pakai_ini = 0;
            if ($pakai_ini == 1) {
                $where_cek = array(
                    "transaksi_id" => $trid,
                    "cabang_id" => $cabang_id,
                );
                $this->db->where($where_cek);
                $queryMaster = $this->db->get($tbl_master)->result();
                showLast_query("kuning");
                if (sizeof($queryMaster) > 0) {
                    $this->db->set('debet', 0);
                    $this->db->set('kredit', 0);
                    $this->db->where($where_cek);
                    $this->db->update($tbl_master);
                    showLast_query("orange");

                    $where_update = array(
                        "id" => $queryMaster[0]->id,
                    );
                    $this->db->set('debet', $debet_detail);
                    $this->db->set('kredit', $kredit_detail);
                    $this->db->where($where_update);
                    $this->db->update($tbl_master);
                    showLast_query("orange");
                }
            }
            //----------------------------
            //update tabel master hpp----------------------------
            $pakai_ini = 0;
            if ($pakai_ini == 1) {
                $where_cek = array(
                    "transaksi_id" => $trid,
                    "cabang_id" => $cabang_id,
                );
                $this->db->where($where_cek);
                $queryMasterHpp = $this->db->get($tbl_master_hpp)->result();
                showLast_query("hitam");
                if (sizeof($queryMasterHpp) > 0) {
                    $this->db->set('debet', 0);
                    $this->db->set('kredit', 0);
                    $this->db->where($where_cek);
                    $this->db->update($tbl_master_hpp);
                    showLast_query("hitam");

                    $where_update = array(
                        "id" => $queryMasterHpp[0]->id,
                    );
                    $this->db->set('debet', $kredit_detail);
                    $this->db->set('kredit', $debet_detail);
                    $this->db->where($where_update);
                    $this->db->update($tbl_master_hpp);
                    showLast_query("hitam");
                }
            }
            //----------------------------
            //update tabel master hutang ke pusat----------------------------
            $pakai_ini = 0;
            if ($pakai_ini == 1) {
                $where_cek = array(
                    "transaksi_id" => $trid,
                    "cabang_id" => $cabang_id,
                );
                $this->db->where($where_cek);
                $queryMasterHkp = $this->db->get($tbl_master_hkp)->result();
                showLast_query("hitam");
                if (sizeof($queryMasterHkp) > 0) {
                    $this->db->set('debet', 0);
                    $this->db->set('kredit', 0);
                    $this->db->where($where_cek);
                    $this->db->update($tbl_master_hkp);
                    showLast_query("hitam");

                    $where_update = array(
                        "id" => $queryMasterHkp[0]->id,
                    );
                    $this->db->set('debet', $kredit_detail);
                    $this->db->set('kredit', $debet_detail);
                    $this->db->where($where_update);
                    $this->db->update($tbl_master_hkp);
                    showLast_query("hitam");
                }
            }
            //----------------------------
            //update tabel master piutang cabang----------------------------
            $pakai_ini = 0;
            if ($pakai_ini == 1) {
                $where_cek = array(
                    "transaksi_id" => $trid,
                    "cabang_id" => $cabang_id,
                );
                $this->db->where($where_cek);
                $queryMasterPiutang = $this->db->get($tbl_master_piutang)->result();
                showLast_query("hitam");
                if (sizeof($queryMasterPiutang) > 0) {
                    $this->db->set('debet', 0);
                    $this->db->set('kredit', 0);
                    $this->db->where($where_cek);
                    $this->db->update($tbl_master_piutang);
                    showLast_query("hitam");

                    $where_update = array(
                        "id" => $queryMasterPiutang[0]->id,
                    );
                    $this->db->set('debet', $kredit_detail);
                    $this->db->set('kredit', $debet_detail);
                    $this->db->where($where_update);
                    $this->db->update($tbl_master_piutang);
                    showLast_query("hitam");
                }
            }
            //----------------------------
//            }
        }
        $selisih_debet = $master_debet_total - $debet_detail_total;
        $selisih_kredit = $master_kredit_total - $kredit_detail_total;
        $master_debet_total_f = number_format($master_debet_total);
        $master_kredit_total_f = number_format($master_kredit_total);
        $debet_detail_total_f = number_format($debet_detail_total);
        $kredit_detail_total_f = number_format($kredit_detail_total);
        $selisih_debet_f = number_format($selisih_debet);
        $selisih_kredit_f = number_format($selisih_kredit);
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_total_f</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_total_f</td>";
        $str .= "</tr>";
        $str .= "</table>";
        $str .= "<div >";
        $str .= "SELISIH DEBET: $selisih_debet_f";
        $str .= "<br>SELISIH KREDIT: $selisih_kredit_f";
        $str .= "</div>";
        echo $str;


        mati_disini("---SETOP--- " . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");


    }

    public function patchMasterValuePiutangCabang()
    {
        $tbl_master_piutang = "__rek_master__1010060010";
        $tbl_master_hkp = "__rek_master__2040010";
        $tbl_master_hpp = "__rek_master__5010";
//        $tbl_master = "__rek_master__1010030030";
//        $tbl_master = "__rek_master__2040010";
//        $tbl_master = "__rek_master__5010";
        $tbl_master = "__rek_master__1010060010";
        $tbl_detail = "__rek_pembantu_produk__1010030030";

        $cabang_id = "-1";
//        $cabang_id = "1";
//        $tahun = "2024";
//        $tahun = "2025";


        $where = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
//            "transaksi_id" => 49290,
            "transaksi_id>" => 5,
            "gudang_id<>" => 0,
        );
        $where_master = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
        );
        //---------------------------------
        $this->db->where($where);
        $queryDetail = $this->db->get($tbl_detail)->result();
        showLast_query("kuning");
//        cekKuning(count($query));
//        mati_disini(__LINE__);
        //---------------------------------
        $detailData = array();
        $trIDs = array();
        foreach ($queryDetail as $spec) {
            $trid = $spec->transaksi_id;
            if ($trid > 0) {
                $trIDs[$trid] = $trid;
            }
            if (!isset($detailData[$trid]["debet"])) {
                $detailData[$trid]["debet"] = 0;
            }
            if (!isset($detailData[$trid]["kredit"])) {
                $detailData[$trid]["kredit"] = 0;
            }
            $detailData[$trid]["debet"] += $spec->debet;
            $detailData[$trid]["kredit"] += $spec->kredit;
        }

        $this->db->where($where_master);
        $this->db->where_in("transaksi_id", $trIDs);
        $queryMaster = $this->db->get($tbl_master)->result();
//        showLast_query("biru");
//        cekBiru(count($query));
        //---------------------------------


        $this->db->trans_start();


        $masterData = array();
        foreach ($queryMaster as $spec) {
            $trid = $spec->transaksi_id;
            $trno = $spec->transaksi_no;
            $debet = $spec->debet;
            $kredit = $spec->kredit;
            $dtime = $spec->dtime;
            $fulldate = $spec->fulldate;
            $debet_detail = isset($detailData[$trid]["debet"]) ? $detailData[$trid]["debet"] : 0;
            $kredit_detail = isset($detailData[$trid]["kredit"]) ? $detailData[$trid]["kredit"] : 0;

            $masterData[$trid]["trid"] = $trid;
            $masterData[$trid]["trno"] = $trno;
            $masterData[$trid]["debet_detail"] = $debet_detail;
            $masterData[$trid]["kredit_detail"] = $kredit_detail;
            $masterData[$trid]["dtime"] = $dtime;
            $masterData[$trid]["fulldate"] = $fulldate;
            if (!isset($masterData[$trid]["master_debet"])) {
                $masterData[$trid]["master_debet"] = 0;
            }
            if (!isset($masterData[$trid]["master_kredit"])) {
                $masterData[$trid]["master_kredit"] = 0;
            }
            $masterData[$trid]["master_debet"] += $debet;
            $masterData[$trid]["master_kredit"] += $kredit;
//            break;
        }
//        arrPrintCyan($masterData[372490]);

        $str = "<table rules='all' width='100%' style='border:1px solid black;'>";
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>No.</td>";
        $str .= "<td>tgl</td>";
        $str .= "<td>trid</td>";
        $str .= "<td>nomer</td>";
        $str .= "<td style='background-color:yellow;'>m debet</td>";
        $str .= "<td style='background-color:#5cb730;'>m kredit</td>";
        $str .= "<td style='background-color:yellow;'>d debet</td>";
        $str .= "<td style='background-color:#5cb730;'>d kredit</td>";
        $str .= "</tr>";
        $no = 0;
        $master_debet_total = 0;
        $master_kredit_total = 0;
        $debet_detail_total = 0;
        $kredit_detail_total = 0;
        foreach ($masterData as $trid => $tridspec) {
            $fulldate = $tridspec["fulldate"];
            $nomer = $tridspec["trno"];
            $master_debet = $tridspec["master_debet"];
            $master_kredit = $tridspec["master_kredit"];
            $debet_detail = $tridspec["debet_detail"];
            $kredit_detail = $tridspec["kredit_detail"];
            // persediaan
//            $selisih_debet_cek = $master_debet - $debet_detail;
//            $selisih_kredit_cek = $master_kredit - $kredit_detail;
            // HPP
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // Hutang ke pusat
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // PIUTANG CABANG
            $selisih_debet_cek = $master_kredit - $debet_detail;
            $selisih_kredit_cek = $master_debet - $kredit_detail;

            $selisih_debet_cek = ($selisih_debet_cek < 0) ? ($selisih_debet_cek * -1) : $selisih_debet_cek;
            $selisih_kredit_cek = ($selisih_kredit_cek < 0) ? ($selisih_kredit_cek * -1) : $selisih_kredit_cek;
//            cekUngu("[$selisih_debet_cek] [$selisih_kredit_cek]");
            if (($selisih_debet_cek > 100) || ($selisih_kredit_cek > 100)) {
                $master_debet_total += $master_debet;
                $master_kredit_total += $master_kredit;
                $debet_detail_total += $debet_detail;
                $kredit_detail_total += $kredit_detail;
                $master_debet_f = number_format($master_debet);
                $master_kredit_f = number_format($master_kredit);
                $debet_detail_f = number_format($debet_detail);
                $kredit_detail_f = number_format($kredit_detail);
                $no++;
                $str .= "<tr>";
                $str .= "<td>$no</td>";
                $str .= "<td>$fulldate</td>";
                $str .= "<td>$trid</td>";
                $str .= "<td>$nomer</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_f</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_f</td>";
                $str .= "</tr>";

                //update tabel master persediaan----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMaster = $this->db->get($tbl_master)->result();
                    showLast_query("kuning");
                    if (sizeof($queryMaster) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master);
                        showLast_query("orange");

                        $where_update = array(
                            "id" => $queryMaster[0]->id,
                        );
                        $this->db->set('debet', $debet_detail);
                        $this->db->set('kredit', $kredit_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master);
                        showLast_query("orange");
                    }
                }
                //----------------------------
                //update tabel master hpp----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHpp = $this->db->get($tbl_master_hpp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHpp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHpp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master hutang ke pusat----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHkp = $this->db->get($tbl_master_hkp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHkp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHkp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master piutang cabang----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterPiutang = $this->db->get($tbl_master_piutang)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterPiutang) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterPiutang[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
            }
        }
        $selisih_debet = $master_debet_total - $debet_detail_total;
        $selisih_kredit = $master_kredit_total - $kredit_detail_total;
        $master_debet_total_f = number_format($master_debet_total);
        $master_kredit_total_f = number_format($master_kredit_total);
        $debet_detail_total_f = number_format($debet_detail_total);
        $kredit_detail_total_f = number_format($kredit_detail_total);
        $selisih_debet_f = number_format($selisih_debet);
        $selisih_kredit_f = number_format($selisih_kredit);
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_total_f</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_total_f</td>";
        $str .= "</tr>";
        $str .= "</table>";
        $str .= "<div >";
        $str .= "SELISIH DEBET: $selisih_debet_f";
        $str .= "<br>SELISIH KREDIT: $selisih_kredit_f";
        $str .= "</div>";
        echo $str;


        mati_disini("---SETOP--- " . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");


    }

    public function patchMasterValueHpp()
    {
        $tbl_master_piutang = "__rek_master__1010060010";
        $tbl_master_hkp = "__rek_master__2040010";
        $tbl_master_hpp = "__rek_master__5010";
//        $tbl_master = "__rek_master__1010030030";
//        $tbl_master = "__rek_master__2040010";
        $tbl_master = "__rek_master__5010";
//        $tbl_master = "__rek_master__1010060010";
        $tbl_detail = "__rek_pembantu_produk__1010030030";

        $cabang_id = "1";
//        $cabang_id = "1";
//        $tahun = "2024";
//        $tahun = "2025";


        $where = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
//            "transaksi_id" => 49290,
            "transaksi_id>" => 5,
            "gudang_id<>" => 0,
        );
        $where_master = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
        );
        //---------------------------------
        $this->db->where($where);
        $queryDetail = $this->db->get($tbl_detail)->result();
        showLast_query("kuning");
//        cekKuning(count($query));
//        mati_disini(__LINE__);
        //---------------------------------
        $detailData = array();
        $trIDs = array();
        foreach ($queryDetail as $spec) {
            $trid = $spec->transaksi_id;
            if ($trid > 0) {
                $trIDs[$trid] = $trid;
            }
            if (!isset($detailData[$trid]["debet"])) {
                $detailData[$trid]["debet"] = 0;
            }
            if (!isset($detailData[$trid]["kredit"])) {
                $detailData[$trid]["kredit"] = 0;
            }
            $detailData[$trid]["debet"] += $spec->debet;
            $detailData[$trid]["kredit"] += $spec->kredit;
        }

        $this->db->where($where_master);
        $this->db->where_in("transaksi_id", $trIDs);
        $queryMaster = $this->db->get($tbl_master)->result();
//        showLast_query("biru");
//        cekBiru(count($query));
        //---------------------------------


        $this->db->trans_start();


        $masterData = array();
        foreach ($queryMaster as $spec) {
            $trid = $spec->transaksi_id;
            $trno = $spec->transaksi_no;
            $debet = $spec->debet;
            $kredit = $spec->kredit;
            $dtime = $spec->dtime;
            $fulldate = $spec->fulldate;
            $debet_detail = isset($detailData[$trid]["debet"]) ? $detailData[$trid]["debet"] : 0;
            $kredit_detail = isset($detailData[$trid]["kredit"]) ? $detailData[$trid]["kredit"] : 0;

            $masterData[$trid]["trid"] = $trid;
            $masterData[$trid]["trno"] = $trno;
            $masterData[$trid]["debet_detail"] = $debet_detail;
            $masterData[$trid]["kredit_detail"] = $kredit_detail;
            $masterData[$trid]["dtime"] = $dtime;
            $masterData[$trid]["fulldate"] = $fulldate;
            if (!isset($masterData[$trid]["master_debet"])) {
                $masterData[$trid]["master_debet"] = 0;
            }
            if (!isset($masterData[$trid]["master_kredit"])) {
                $masterData[$trid]["master_kredit"] = 0;
            }
            $masterData[$trid]["master_debet"] += $debet;
            $masterData[$trid]["master_kredit"] += $kredit;
//            break;
        }
//        arrPrintCyan($masterData[372490]);

        $str = "<table rules='all' width='100%' style='border:1px solid black;'>";
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>No.</td>";
        $str .= "<td>tgl</td>";
        $str .= "<td>trid</td>";
        $str .= "<td>nomer</td>";
        $str .= "<td style='background-color:yellow;'>m debet</td>";
        $str .= "<td style='background-color:#5cb730;'>m kredit</td>";
        $str .= "<td style='background-color:yellow;'>d debet</td>";
        $str .= "<td style='background-color:#5cb730;'>d kredit</td>";
        $str .= "</tr>";
        $no = 0;
        $master_debet_total = 0;
        $master_kredit_total = 0;
        $debet_detail_total = 0;
        $kredit_detail_total = 0;
        foreach ($masterData as $trid => $tridspec) {
            $fulldate = $tridspec["fulldate"];
            $nomer = $tridspec["trno"];
            $master_debet = $tridspec["master_debet"];
            $master_kredit = $tridspec["master_kredit"];
            $debet_detail = $tridspec["debet_detail"];
            $kredit_detail = $tridspec["kredit_detail"];
            // persediaan
//            $selisih_debet_cek = $master_debet - $debet_detail;
//            $selisih_kredit_cek = $master_kredit - $kredit_detail;
            // HPP
            $selisih_debet_cek = $master_kredit - $debet_detail;
            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // Hutang ke pusat
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // PIUTANG CABANG
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;

            $selisih_debet_cek = ($selisih_debet_cek < 0) ? ($selisih_debet_cek * -1) : $selisih_debet_cek;
            $selisih_kredit_cek = ($selisih_kredit_cek < 0) ? ($selisih_kredit_cek * -1) : $selisih_kredit_cek;
//            cekUngu("[$selisih_debet_cek] [$selisih_kredit_cek]");
            if (($selisih_debet_cek > 100) || ($selisih_kredit_cek > 100)) {
                $master_debet_total += $master_debet;
                $master_kredit_total += $master_kredit;
                $debet_detail_total += $debet_detail;
                $kredit_detail_total += $kredit_detail;
                $master_debet_f = number_format($master_debet);
                $master_kredit_f = number_format($master_kredit);
                $debet_detail_f = number_format($debet_detail);
                $kredit_detail_f = number_format($kredit_detail);
                $no++;
                $str .= "<tr>";
                $str .= "<td>$no</td>";
                $str .= "<td>$fulldate</td>";
                $str .= "<td>$trid</td>";
                $str .= "<td>$nomer</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_f</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_f</td>";
                $str .= "</tr>";

                //update tabel master persediaan----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMaster = $this->db->get($tbl_master)->result();
                    showLast_query("kuning");
                    if (sizeof($queryMaster) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master);
                        showLast_query("orange");

                        $where_update = array(
                            "id" => $queryMaster[0]->id,
                        );
                        $this->db->set('debet', $debet_detail);
                        $this->db->set('kredit', $kredit_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master);
                        showLast_query("orange");
                    }
                }
                //----------------------------
                //update tabel master hpp----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHpp = $this->db->get($tbl_master_hpp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHpp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHpp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master hutang ke pusat----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHkp = $this->db->get($tbl_master_hkp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHkp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHkp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master piutang cabang----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterPiutang = $this->db->get($tbl_master_piutang)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterPiutang) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterPiutang[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
            }
        }
        $selisih_debet = $master_debet_total - $debet_detail_total;
        $selisih_kredit = $master_kredit_total - $kredit_detail_total;
        $master_debet_total_f = number_format($master_debet_total);
        $master_kredit_total_f = number_format($master_kredit_total);
        $debet_detail_total_f = number_format($debet_detail_total);
        $kredit_detail_total_f = number_format($kredit_detail_total);
        $selisih_debet_f = number_format($selisih_debet);
        $selisih_kredit_f = number_format($selisih_kredit);
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_total_f</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_total_f</td>";
        $str .= "</tr>";
        $str .= "</table>";
        $str .= "<div >";
        $str .= "SELISIH DEBET: $selisih_debet_f";
        $str .= "<br>SELISIH KREDIT: $selisih_kredit_f";
        $str .= "</div>";
        echo $str;


        mati_disini("---SETOP--- " . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");


    }

    public function patchMasterValuePiutangSupplier()
    {
        $tbl_master_piutang = "__rek_master__1010060010";
        $tbl_master_hkp = "__rek_master__2040010";
        $tbl_master_hpp = "__rek_master__5010";
//        $tbl_master = "__rek_master__1010030030";
//        $tbl_master = "__rek_master__2040010";
        $tbl_master = "__rek_master__1010020030";
//        $tbl_master = "__rek_master__5010";
//        $tbl_master = "__rek_master__1010060010";
        $tbl_detail = "__rek_pembantu_produk__1010030030";

        $cabang_id = "-1";
//        $cabang_id = "1";
//        $tahun = "2024";
//        $tahun = "2025";


        $where = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
//            "transaksi_id" => 49290,
            "transaksi_id>" => 5,
            "gudang_id<>" => 0,
            "jenis" => "3333",
        );
        $where_master = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
        );
        //---------------------------------
        $this->db->where($where);
        $queryDetail = $this->db->get($tbl_detail)->result();
        showLast_query("kuning");
//        cekKuning(count($query));
//        mati_disini(__LINE__);
        //---------------------------------
        $detailData = array();
        $trIDs = array();
        foreach ($queryDetail as $spec) {
            $trid = $spec->transaksi_id;
            if ($trid > 0) {
                $trIDs[$trid] = $trid;
            }
            if (!isset($detailData[$trid]["debet"])) {
                $detailData[$trid]["debet"] = 0;
            }
            if (!isset($detailData[$trid]["kredit"])) {
                $detailData[$trid]["kredit"] = 0;
            }
            $detailData[$trid]["debet"] += $spec->debet;
            $detailData[$trid]["kredit"] += $spec->kredit;
        }

        $this->db->where($where_master);
        $this->db->where_in("transaksi_id", $trIDs);
        $queryMaster = $this->db->get($tbl_master)->result();
//        showLast_query("biru");
//        cekBiru(count($query));
        //---------------------------------


        $this->db->trans_start();


        $masterData = array();
        foreach ($queryMaster as $spec) {
            $trid = $spec->transaksi_id;
            $trno = $spec->transaksi_no;
            $debet = $spec->debet;
            $kredit = $spec->kredit;
            $dtime = $spec->dtime;
            $fulldate = $spec->fulldate;
            $debet_detail = isset($detailData[$trid]["debet"]) ? $detailData[$trid]["debet"] : 0;
            $kredit_detail = isset($detailData[$trid]["kredit"]) ? $detailData[$trid]["kredit"] : 0;

            $masterData[$trid]["trid"] = $trid;
            $masterData[$trid]["trno"] = $trno;
            $masterData[$trid]["debet_detail"] = $debet_detail;
            $masterData[$trid]["kredit_detail"] = $kredit_detail;
            $masterData[$trid]["dtime"] = $dtime;
            $masterData[$trid]["fulldate"] = $fulldate;
            if (!isset($masterData[$trid]["master_debet"])) {
                $masterData[$trid]["master_debet"] = 0;
            }
            if (!isset($masterData[$trid]["master_kredit"])) {
                $masterData[$trid]["master_kredit"] = 0;
            }
            $masterData[$trid]["master_debet"] += $debet;
            $masterData[$trid]["master_kredit"] += $kredit;
//            break;
        }
//        arrPrintCyan($masterData[372490]);

        $str = "<table rules='all' width='100%' style='border:1px solid black;'>";
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>No.</td>";
        $str .= "<td>tgl</td>";
        $str .= "<td>trid</td>";
        $str .= "<td>nomer</td>";
        $str .= "<td style='background-color:yellow;'>m debet</td>";
        $str .= "<td style='background-color:#5cb730;'>m kredit</td>";
        $str .= "<td style='background-color:yellow;'>d debet</td>";
        $str .= "<td style='background-color:#5cb730;'>d kredit</td>";
        $str .= "</tr>";
        $no = 0;
        $master_debet_total = 0;
        $master_kredit_total = 0;
        $debet_detail_total = 0;
        $kredit_detail_total = 0;
        foreach ($masterData as $trid => $tridspec) {
            $fulldate = $tridspec["fulldate"];
            $nomer = $tridspec["trno"];
            $master_debet = $tridspec["master_debet"];
            $master_kredit = $tridspec["master_kredit"];
            $debet_detail = $tridspec["debet_detail"];
            $kredit_detail = $tridspec["kredit_detail"];
            // persediaan
//            $selisih_debet_cek = $master_debet - $debet_detail;
//            $selisih_kredit_cek = $master_kredit - $kredit_detail;
            // HPP
            $selisih_debet_cek = $master_kredit - $debet_detail;
            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // Hutang ke pusat
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // PIUTANG CABANG
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;

            $selisih_debet_cek = ($selisih_debet_cek < 0) ? ($selisih_debet_cek * -1) : $selisih_debet_cek;
            $selisih_kredit_cek = ($selisih_kredit_cek < 0) ? ($selisih_kredit_cek * -1) : $selisih_kredit_cek;
//            cekUngu("[$selisih_debet_cek] [$selisih_kredit_cek]");
            if (($selisih_debet_cek > 100) || ($selisih_kredit_cek > 100)) {
                $master_debet_total += $master_debet;
                $master_kredit_total += $master_kredit;
                $debet_detail_total += $debet_detail;
                $kredit_detail_total += $kredit_detail;
                $master_debet_f = number_format($master_debet);
                $master_kredit_f = number_format($master_kredit);
                $debet_detail_f = number_format($debet_detail);
                $kredit_detail_f = number_format($kredit_detail);
                $no++;
                $str .= "<tr>";
                $str .= "<td>$no</td>";
                $str .= "<td>$fulldate</td>";
                $str .= "<td>$trid</td>";
                $str .= "<td>$nomer</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_f</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_f</td>";
                $str .= "</tr>";

                //update tabel master persediaan----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMaster = $this->db->get($tbl_master)->result();
                    showLast_query("kuning");
                    if (sizeof($queryMaster) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master);
                        showLast_query("orange");

                        $where_update = array(
                            "id" => $queryMaster[0]->id,
                        );
                        $this->db->set('debet', $debet_detail);
                        $this->db->set('kredit', $kredit_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master);
                        showLast_query("orange");
                    }
                }
                //----------------------------
                //update tabel master hpp----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHpp = $this->db->get($tbl_master_hpp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHpp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHpp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master hutang ke pusat----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHkp = $this->db->get($tbl_master_hkp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHkp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHkp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master piutang cabang----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterPiutang = $this->db->get($tbl_master_piutang)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterPiutang) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterPiutang[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
            }
        }
        $selisih_debet = $master_debet_total - $debet_detail_total;
        $selisih_kredit = $master_kredit_total - $kredit_detail_total;
        $master_debet_total_f = number_format($master_debet_total);
        $master_kredit_total_f = number_format($master_kredit_total);
        $debet_detail_total_f = number_format($debet_detail_total);
        $kredit_detail_total_f = number_format($kredit_detail_total);
        $selisih_debet_f = number_format($selisih_debet);
        $selisih_kredit_f = number_format($selisih_kredit);
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_total_f</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_total_f</td>";
        $str .= "</tr>";
        $str .= "</table>";
        $str .= "<div >";
        $str .= "SELISIH DEBET: $selisih_debet_f";
        $str .= "<br>SELISIH KREDIT: $selisih_kredit_f";
        $str .= "</div>";
        echo $str;


        mati_disini("---SETOP--- " . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");


    }

    // master cek ke detail
    public function patchMasterValue2()
    {
        $this->load->model("MdlTransaksi");
        $tr = New MdlTransaksi();

        $tbl_master_piutang = "__rek_master__1010060010";
        $tbl_master_hkp = "__rek_master__2040010";
        $tbl_master_hpp = "__rek_master__5010";
//        $tbl_master = "__rek_master__1010030030";// persediaan
//        $tbl_master = "__rek_master__2040010";// hutang ke pusat
//        $tbl_master = "__rek_master__5010";// hpp
        $tbl_master = "__rek_master__1010060010";// piutang cabang
        $tbl_detail = "__rek_pembantu_produk__1010030030";
        $jenis = "585";

        $cabang_id = isset($_GET["w"]) ? $_GET["w"] : "-1";
//        $cabang_id = "1";
//        $tahun = "2024";
//        $tahun = "2025";

        //---------------------------------
        $trID_trans = array();
        $tr->addFilter("jenis='$jenis'");
//        $tr->addFilter("cabang_id='$cabang_id'");
        $trTmp = $tr->lookupAll()->result();
        showLast_query("biru");
        if (sizeof($trTmp) > 0) {
            foreach ($trTmp as $trSpec) {
                $trID_trans[$trSpec->id] = $trSpec->id;
            }
        }
        //---------------------------------


        $where = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
//            "transaksi_id" => 49290,
//            "transaksi_id>" => 5,
            "gudang_id<>" => 0,
//            "jenis" => "467",
        );
        $where_master = array(
            "cabang_id" => $cabang_id,
//            "year(dtime)" => $tahun,
            "jenis" => "$jenis",
        );
        //---------------------------------
        $this->db->where($where_master);
        $this->db->where_in("transaksi_id", $trID_trans);
        $queryMaster = $this->db->get($tbl_master)->result();
//        showLast_query("hitam");
//        cekBiru(count($query));
        $trIDs = array();
        foreach ($queryMaster as $spec) {
            $trid = $spec->transaksi_id;
            $trIDs[$trid] = $trid;
//            if ($trid > 0) {
//            }
        }
        //---------------------------------


        $this->db->where($where);
        $this->db->where_in("transaksi_id", $trID_trans);
        $queryDetail = $this->db->get($tbl_detail)->result();
//        showLast_query("kuning");
//        cekKuning(count($query));
//        mati_disini(__LINE__);
        //---------------------------------
        $detailData = array();
//        $trIDs = array();
        foreach ($queryDetail as $spec) {
            $trid = $spec->transaksi_id;
//            if ($trid > 0) {
//                $trIDs[$trid] = $trid;
//            }
//
            if (!isset($detailData[$trid]["debet"])) {
                $detailData[$trid]["debet"] = 0;
            }
            if (!isset($detailData[$trid]["kredit"])) {
                $detailData[$trid]["kredit"] = 0;
            }
            $detailData[$trid]["debet"] += $spec->debet;
            $detailData[$trid]["kredit"] += $spec->kredit;
        }


        $this->db->trans_start();


        $masterData = array();
        foreach ($queryMaster as $spec) {
            $trid = $spec->transaksi_id;
            $trno = $spec->transaksi_no;
            $debet = $spec->debet;
            $kredit = $spec->kredit;
            $dtime = $spec->dtime;
            $fulldate = $spec->fulldate;
            $debet_detail = isset($detailData[$trid]["debet"]) ? $detailData[$trid]["debet"] : 0;
            $kredit_detail = isset($detailData[$trid]["kredit"]) ? $detailData[$trid]["kredit"] : 0;

            $masterData[$trid]["trid"] = $trid;
            $masterData[$trid]["trno"] = $trno;
            $masterData[$trid]["debet_detail"] = $debet_detail;
            $masterData[$trid]["kredit_detail"] = $kredit_detail;
            $masterData[$trid]["dtime"] = $dtime;
            $masterData[$trid]["fulldate"] = $fulldate;
            if (!isset($masterData[$trid]["master_debet"])) {
                $masterData[$trid]["master_debet"] = 0;
            }
            if (!isset($masterData[$trid]["master_kredit"])) {
                $masterData[$trid]["master_kredit"] = 0;
            }
            $masterData[$trid]["master_debet"] += $debet;
            $masterData[$trid]["master_kredit"] += $kredit;
//            break;
        }
//        arrPrintCyan($masterData[372490]);

        $str = "<table rules='all' width='100%' style='border:1px solid black;'>";
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>No.</td>";
        $str .= "<td>tgl</td>";
        $str .= "<td>trid</td>";
        $str .= "<td>nomer</td>";
        $str .= "<td style='background-color:yellow;'>m debet</td>";
        $str .= "<td style='background-color:#5cb730;'>m kredit</td>";
        $str .= "<td style='background-color:yellow;'>d debet</td>";
        $str .= "<td style='background-color:#5cb730;'>d kredit</td>";
        $str .= "</tr>";
        $no = 0;
        $master_debet_total = 0;
        $master_kredit_total = 0;
        $debet_detail_total = 0;
        $kredit_detail_total = 0;
        foreach ($trID_trans as $trid => $xxx) {
//        foreach ($masterData as $trid => $tridspec) {
            $tridspec = $masterData[$trid];
            $fulldate = $tridspec["fulldate"];
            $nomer = $tridspec["trno"];
            $master_debet = $tridspec["master_debet"];
            $master_kredit = $tridspec["master_kredit"];
            $debet_detail = $tridspec["debet_detail"];
            $kredit_detail = $tridspec["kredit_detail"];
            $netto_master = $master_debet - $master_kredit;
            $netto_detail = $debet_detail - $kredit_detail;
            // persediaan
//            $selisih_debet_cek = $master_debet - $debet_detail;
//            $selisih_kredit_cek = $debet_detail - $kredit_detail;
//            $selisih_netto_cek = $netto_master - $netto_detail;
            // HPP
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // Hutang ke pusat
//            $selisih_debet_cek = $master_kredit - $debet_detail;
//            $selisih_kredit_cek = $master_debet - $kredit_detail;
            // PIUTANG CABANG
            $selisih_debet_cek = $master_kredit - $debet_detail;
            $selisih_kredit_cek = $master_debet - $kredit_detail;

            $selisih_debet_cek = ($selisih_debet_cek < 0) ? ($selisih_debet_cek * -1) : $selisih_debet_cek;
            $selisih_kredit_cek = ($selisih_kredit_cek < 0) ? ($selisih_kredit_cek * -1) : $selisih_kredit_cek;
//            $selisih_netto_cek = ($selisih_netto_cek < 0) ? ($selisih_netto_cek * -1) : $selisih_netto_cek;
//            cekUngu("[$selisih_debet_cek] [$selisih_kredit_cek]");
            if (($selisih_debet_cek > 100) || ($selisih_kredit_cek > 100)) {
//            if ($selisih_netto_cek > 100) {
                $master_debet_total += $master_debet;
                $master_kredit_total += $master_kredit;
                $debet_detail_total += $debet_detail;
                $kredit_detail_total += $kredit_detail;
                $master_debet_f = number_format($master_debet);
                $master_kredit_f = number_format($master_kredit);
                $debet_detail_f = number_format($debet_detail);
                $kredit_detail_f = number_format($kredit_detail);
                $no++;
                $str .= "<tr>";
                $str .= "<td>$no</td>";
                $str .= "<td>$fulldate</td>";
                $str .= "<td>$trid</td>";
                $str .= "<td>$nomer</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_f</td>";
                $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_f</td>";
                $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_f</td>";
                $str .= "</tr>";

                //update tabel master persediaan----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMaster = $this->db->get($tbl_master)->result();
                    showLast_query("kuning");
                    if (sizeof($queryMaster) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master);
                        showLast_query("orange");

                        $where_update = array(
                            "id" => $queryMaster[0]->id,
                        );
                        $this->db->set('debet', $debet_detail);
                        $this->db->set('kredit', $kredit_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master);
                        showLast_query("orange");
                    }
                }
                //----------------------------
                //update tabel master hpp----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHpp = $this->db->get($tbl_master_hpp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHpp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHpp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hpp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master hutang ke pusat----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterHkp = $this->db->get($tbl_master_hkp)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterHkp) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterHkp[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_hkp);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
                //update tabel master piutang cabang----------------------------
                $pakai_ini = 0;
                if ($pakai_ini == 1) {
                    $where_cek = array(
                        "transaksi_id" => $trid,
                        "cabang_id" => $cabang_id,
                    );
                    $this->db->where($where_cek);
                    $queryMasterPiutang = $this->db->get($tbl_master_piutang)->result();
                    showLast_query("hitam");
                    if (sizeof($queryMasterPiutang) > 0) {
                        $this->db->set('debet', 0);
                        $this->db->set('kredit', 0);
                        $this->db->where($where_cek);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");

                        $where_update = array(
                            "id" => $queryMasterPiutang[0]->id,
                        );
                        $this->db->set('debet', $kredit_detail);
                        $this->db->set('kredit', $debet_detail);
                        $this->db->where($where_update);
                        $this->db->update($tbl_master_piutang);
                        showLast_query("hitam");
                    }
                }
                //----------------------------
            }
        }
        $selisih_debet = $master_debet_total - $debet_detail_total;
        $selisih_kredit = $master_kredit_total - $kredit_detail_total;
        $master_debet_total_f = number_format($master_debet_total);
        $master_kredit_total_f = number_format($master_kredit_total);
        $debet_detail_total_f = number_format($debet_detail_total);
        $kredit_detail_total_f = number_format($kredit_detail_total);
        $selisih_debet_f = number_format($selisih_debet);
        $selisih_kredit_f = number_format($selisih_kredit);
        $str .= "<tr style='text-align:center;font-size:15px;font-weight:bold;'>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td>-</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$master_debet_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$master_kredit_total_f</td>";
        $str .= "<td style='background-color:yellow;text-align:right;'>$debet_detail_total_f</td>";
        $str .= "<td style='background-color:#5cb730;text-align:right;'>$kredit_detail_total_f</td>";
        $str .= "</tr>";
        $str .= "</table>";
        $str .= "<div >";
        $str .= "SELISIH DEBET: $selisih_debet_f";
        $str .= "<br>SELISIH KREDIT: $selisih_kredit_f";
        $str .= "</div>";
        echo $str;

//        arrPrint($trIDs);
//        cekHere("jumlah trid transaksi: " . count($trID_trans));
//        cekBiru("jumlah trid rekening: " . count($trIDs));
//        $arrDiffTrIDs = array_diff($trID_trans, $trIDs);
//        if(sizeof($arrDiffTrIDs)>0){
//            $arrTrDataDetail = array();
//            $this->db->where_in("transaksi_id", $arrDiffTrIDs);
//            $queryData = $this->db->get("transaksi_data")->result();
//            foreach ($queryData as $dataSpec){
//                $arrTrDataDetail[$dataSpec->transaksi_id][] = array(
//                    "produk_id" => $dataSpec->produk_id,
//                    "produk_nama" => $dataSpec->produk_nama,
//                );
//            }
//            arrPrintCyan($arrTrDataDetail);
//        }

        mati_disini("---SETOP--- " . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");


    }

    public function cek2()
    {
        $this->load->model("MdlTransaksi");
        $tr = New MdlTransaksi();

        $tbl_master_pc = "__rek_master__1010060010";
        $tbl_master_hkp = "__rek_master__2040010";
        $tbl_master_hpp = "__rek_master__5010";
        $tbl_master = "__rek_master__1010030030";// persediaan
        $cabang_id = "1";
        $jenis = "585";
        $arrHkp = array();
        $arrPc = array();
        $arrPersediaan = array();
        $arrHpp = array();

        //HUTANG KE PUSAT---------------------------------
        $where_hkp = array(
//            "cabang_id" => $cabang_id,
            "jenis" => $jenis,
        );
        $this->db->where($where_hkp);
        $queryHkp = $this->db->get($tbl_master_hkp)->result();
        showLast_query("hitam");
        foreach ($queryHkp as $specHkp) {
            $trid = $specHkp->transaksi_id;
            if (!isset($arrHkp[$trid]["debet"])) {
                $arrHkp[$trid]["debet"] = 0;
            }
            if (!isset($arrHkp[$trid]["kredit"])) {
                $arrHkp[$trid]["kredit"] = 0;
            }
            $arrHkp[$trid]["debet"] += $specHkp->debet;
            $arrHkp[$trid]["kredit"] += $specHkp->kredit;
        }

        //PIUTANG CABANG---------------------------------
        $where_pc = array(
//            "cabang_id" => $cabang_id,
            "jenis" => $jenis,
        );
        $this->db->where($where_pc);
        $queryPc = $this->db->get($tbl_master_pc)->result();
        showLast_query("hitam");
        foreach ($queryPc as $specPc) {
            $trid = $specPc->transaksi_id;
            if (!isset($arrPc[$trid]["debet"])) {
                $arrPc[$trid]["debet"] = 0;
            }
            if (!isset($arrPc[$trid]["kredit"])) {
                $arrPc[$trid]["kredit"] = 0;
            }
            $arrPc[$trid]["debet"] += $specPc->debet;
            $arrPc[$trid]["kredit"] += $specPc->kredit;
        }

        //PERSEDIAAN---------------------------------
        $where = array(
//            "cabang_id" => $cabang_id,
            "jenis" => $jenis,
        );
        $this->db->where($where);
        $query = $this->db->get($tbl_master)->result();
        showLast_query("hitam");
        foreach ($query as $spec) {
            $trid = $spec->transaksi_id;
            if (!isset($arrPersediaan[$trid]["debet"])) {
                $arrPersediaan[$trid]["debet"] = 0;
            }
            if (!isset($arrPersediaan[$trid]["kredit"])) {
                $arrPersediaan[$trid]["kredit"] = 0;
            }
            $arrPersediaan[$trid]["debet"] += $spec->debet;
            $arrPersediaan[$trid]["kredit"] += $spec->kredit;
        }

        //HPP---------------------------------
        $where_hpp = array(
//            "cabang_id" => $cabang_id,
            "jenis" => $jenis,
        );
        $this->db->where($where_hpp);
        $queryHpp = $this->db->get($tbl_master_hpp)->result();
        showLast_query("hitam");
        foreach ($queryHpp as $specHpp) {
            $trid = $specHpp->transaksi_id;
            if (!isset($arrHpp[$trid]["debet"])) {
                $arrHpp[$trid]["debet"] = 0;
            }
            if (!isset($arrHpp[$trid]["kredit"])) {
                $arrHpp[$trid]["kredit"] = 0;
            }
            $arrHpp[$trid]["debet"] += $specHpp->debet;
            $arrHpp[$trid]["kredit"] += $specHpp->kredit;
        }

        // PERSEDIAAN vs PIUTANG CABANG
        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            if (sizeof($arrPersediaan) > 0) {
                foreach ($arrPersediaan as $tr => $specPersediaan) {
                    $debetPersediaan = $specPersediaan["debet"];
                    $kreditPersediaan = $specPersediaan["kredit"];
                    $debetPc = isset($arrPc[$tr]["debet"]) ? $arrPc[$tr]["debet"] : 0;
                    $kreditPc = isset($arrPc[$tr]["kredit"]) ? $arrPc[$tr]["kredit"] : 0;
                    // kredit hkp == debet pc
                    $selisih_persediaan_pc = $kreditPersediaan - $debetPc;
                    $selisih_persediaan_pc = ($selisih_persediaan_pc < 0) ? ($selisih_persediaan_pc * -1) : $selisih_persediaan_pc;
                    if ($selisih_persediaan_pc > 10) {
                        cekHere("[trid: [$tr]] [pc debet: $debetPc] [persediaan kredit: $kreditPersediaan]");
                    }
                }
            }
        }

        // PERSEDIAAN vs HUTANG KE PUSAT
        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            if (sizeof($arrPersediaan) > 0) {
                foreach ($arrPersediaan as $tr => $specPersediaan) {
                    $debetPersediaan = $specPersediaan["debet"];
                    $kreditPersediaan = $specPersediaan["kredit"];
                    $debetHkp = isset($arrHkp[$tr]["debet"]) ? $arrHkp[$tr]["debet"] : 0;
                    $kreditHkp = isset($arrHkp[$tr]["kredit"]) ? $arrHkp[$tr]["kredit"] : 0;
                    // kredit hkp == debet pc
                    $selisih_persediaan_hkp = $debetPersediaan - $kreditHkp;
                    $selisih_persediaan_hkp = ($selisih_persediaan_hkp < 0) ? ($selisih_persediaan_hkp * -1) : $selisih_persediaan_hkp;
                    if ($selisih_persediaan_hkp > 10) {
                        cekHere("[trid: [$tr]] [hkp kredit: $kreditHkp] [persediaan debet: $debetPersediaan]");
                    }
//                    else{
//                        cekHijau("[trid: $tr] [selisih_persediaan_hkp: $selisih_persediaan_hkp]");
//                    }
                }
            }
        }

        // HUTANG KE PUSAT vs PIUTANG CABANG
        $pakai_ini = 1;
        if ($pakai_ini == 1) {
            if (sizeof($arrHkp) > 0) {
                foreach ($arrHkp as $tr => $specHkp) {
                    $debetHkp = $specHkp["debet"];
                    $kreditHkp = $specHkp["kredit"];
                    $debetPc = isset($arrPc[$tr]["debet"]) ? $arrPc[$tr]["debet"] : 0;
                    $kreditPc = isset($arrPc[$tr]["kredit"]) ? $arrPc[$tr]["kredit"] : 0;
                    // kredit hkp == debet pc
                    $selisih_hkp_pc = $kreditHkp - $debetPc;
                    $selisih_hkp_pc = ($selisih_hkp_pc < 0) ? ($selisih_hkp_pc * -1) : $selisih_hkp_pc;
                    if ($selisih_hkp_pc > 10) {
                        cekHere("[trid: [$tr]] [pc debet: $debetPc] [hkp kredit: $kreditHkp]");
                    }
                }
            }
        }


    }

    public function cekLockerDiskon()
    {
        $this->load->model("Mdls/MdlLockerStockDiskonVendor");
        $this->load->model("Coms/ComRekeningPembantuPiutangSupplierDetailTransItem");

        $jenis = "diskon";
        $state = "active";
        $arrLockerDiskon = array();
        $arrDetailDiskon = array();
        $ld = New MdlLockerStockDiskonVendor();
        $ld->addFilter("jenis='$jenis'");
        $ld->addFilter("state='$state'");
        $ld->addFilter("nilai>0");
        $ldTmp = $ld->lookupAll()->result();
        showLast_query("biru");
        foreach ($ldTmp as $ldSpec) {
            $tr_id = $ldSpec->transaksi_id;
            $extern_id = $ldSpec->extern_id;
            $supplier_id = $ldSpec->supplier_id;
            $nilai = $ldSpec->nilai;
            if (!isset($arrLockerDiskon[$supplier_id][$tr_id][$extern_id])) {
                $arrLockerDiskon[$supplier_id][$tr_id][$extern_id] = 0;
            }
            $arrLockerDiskon[$supplier_id][$tr_id][$extern_id] += $nilai;
        }


        $dd = New ComRekeningPembantuPiutangSupplierDetailTransItem();
        $dd->addFilter("periode='forever'");
        $dd->addFilter("rekening='1010020030'");
        $ddTmp = $dd->lookupAll()->result();
        showLast_query("kuning");
        foreach ($ddTmp as $ddSpec) {
            $tr_id = $ddSpec->extern_id;
            $extern_id = $ddSpec->extern2_id;
            $supplier_id = $ddSpec->extern3_id;
            $nilai = $ddSpec->debet;
            if (!isset($arrDetailDiskon[$supplier_id][$tr_id][$extern_id])) {
                $arrDetailDiskon[$supplier_id][$tr_id][$extern_id] = 0;
            }
            $arrDetailDiskon[$supplier_id][$tr_id][$extern_id] += $nilai;
        }


        $this->db->trans_start();


        $no = 0;
        foreach ($arrLockerDiskon as $sup_id => $supSpec) {
            foreach ($supSpec as $trid => $trSpec) {
                foreach ($trSpec as $extid => $nilai_locker) {
                    $nilai_rek = isset($arrDetailDiskon[$sup_id][$trid][$extid]) ? $arrDetailDiskon[$sup_id][$trid][$extid] : 0;
                    $selisih = $nilai_locker - $nilai_rek;
                    if ($selisih > 100) {
                        $no++;
                        cekMerah("[$no] [supplierID: $sup_id] [transaksiID: $trid] [diskonID: $extid] [locker: $nilai_locker] [rek: $nilai_rek]");
                        $where = array(
                            "jenis" => $jenis,
                            "state" => $state,
                            "supplier_id" => $sup_id,
                            "transaksi_id" => $trid,
                            "extern_id" => $extid,
                        );
                        $data = array(
                            "nilai" => 0,
                        );
                        $ld = New MdlLockerStockDiskonVendor();
                        $ld->setFilters(array());
                        $ld->updateData($where, $data);
                        showLast_query("orange");
                    }
                }
            }
        }


        mati_disini("---SETOP--- " . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");


    }

    public function cekSerial()
    {
        $tbl = "_rek_pembantu_produk_perserial_cache";
        $cabang_id = "-1";
        $gudang_id = "9";
        $this->db->where('cabang_id', $cabang_id);
        $this->db->where('gudang_id', $gudang_id);
        $query = $this->db->get($tbl)->result();
        showLast_query("biru");
        $arrSerial = array();
        foreach ($query as $spec) {
            $arrSerial[$spec->extern_nama][] = 1;
        }
        foreach ($arrSerial as $serial => $xx) {
            if (count($xx) > 1) {
                arrPrint($xx);
                cekHitam("$serial");
            }
        }
    }

    public function patchRebateSupplier()
    {
        $this->load->model("MdlTransaksi");
        $this->load->model("Mdls/MdlSupplierDiskon");
        $this->load->model("Mdls/MdlLockerStockDiskonVendor");
        $arrDataDiskon = array();
        //region data diskon-----
        $ds = New MdlSupplierDiskon();
        $dsTmp = $ds->lookupAll()->result();
        foreach ($dsTmp as $dsSpec) {
            $arrDiskonData[$dsSpec->id] = $dsSpec->label;
        }
        //endregion-----

        // region jenis 3333
        $arrData = array();
        $tr = New MdlTransaksi();
        $tr->addFilter("jenis='3333'");
        $trTmp = $tr->lookupAll()->result();
//        cekHere(count($trTmp));
        if (sizeof($trTmp) > 0) {
            foreach ($trTmp as $trSpec) {
                $ini_trid = $trSpec->id;
                $trreg = New MdlTransaksi();
                $trreg->setFilters(array());
                $trreg->setJointSelectFields("transaksi_id, main");
                $trreg->addFilter("transaksi_id='$ini_trid'");
                $tmpReg = $trreg->lookupDataRegistries()->result();
                $main = blobDecode($tmpReg[0]->main);
//                arrPrint($main);
                $pihakMainReferenceJenis = $main["pihakMainReferenceJenis"];
                $pihakMainID = ($main["pihakMainID"] > 0) ? $main["pihakMainID"] : 0;
                $pihakMainName = $main["pihakMainName"];

                if ($pihakMainID > 0) {
                    if ($pihakMainID < 100) {
                        foreach ($main["refIDs"] as $vv) {
                            $pihakMainID = $vv;
                            $pihakMainName = $vv;
                            $pihakMainReferenceJenis = 0;
                        }
                        if ($pihakMainReferenceJenis == NULL) {
                            $tr = New MdlTransaksi();
                            $tr->addFilter("id='$pihakMainID'");
                            $trTmp = $tr->lookupAll()->result();
                            $pihakMainReferenceJenis = $trTmp[0]->jenis;
                        }
                        $arrData[$ini_trid] = array(
                            "reference_id" => $pihakMainID,
                            "reference_nomer" => $pihakMainName,
                            "reference_jenis" => $pihakMainReferenceJenis,
                        );
                    }
                    else {
                        if ($pihakMainReferenceJenis == NULL) {
                            $tr = New MdlTransaksi();
                            $tr->addFilter("id='$pihakMainID'");
                            $trTmp = $tr->lookupAll()->result();
                            $pihakMainReferenceJenis = $trTmp[0]->jenis;
                        }
                        $arrData[$ini_trid] = array(
                            "reference_id" => $pihakMainID,
                            "reference_nomer" => $pihakMainName,
                            "reference_jenis" => $pihakMainReferenceJenis,
                        );
                    }
                }
            }

        }
        // endregion jenis 3333

        // region locker diskon
        $arrTrID_locker = array();
        $arrTrData_locker = array();
        $arrTrData_locker_total = array();
        $ld = New MdlLockerStockDiskonVendor();
        $ld->addFilter("nilai>'0'");
        $ldTmp = $ld->lookupAll()->result();
        if (sizeof($ldTmp) > 0) {
            foreach ($ldTmp as $ii => $ldSpec) {
                $arrTrID_locker[$ldSpec->transaksi_id] = $ldSpec->transaksi_id;
                $arrTrData_locker[$ldSpec->transaksi_id][$ldSpec->extern2_id][$ldSpec->extern_id] = array(
                    "supplier_id" => $ldSpec->supplier_id,
                    "supplier_nama" => $ldSpec->supplier_nama,
                    "produk_id" => $ldSpec->produk_id,
                    "produk_nama" => $ldSpec->produk_nama,
                    "extern_id" => $ldSpec->extern_id,
                    "extern_nama" => $ldSpec->extern_nama,
                    "extern2_id" => $ldSpec->extern2_id,
                    "extern2_nama" => $ldSpec->extern2_nama,
                    "nilai" => $ldSpec->nilai,
                    "nilai_unit" => $ldSpec->nilai_unit,
                    "jumlah" => $ldSpec->jumlah,
                    "id_tbl" => $ldSpec->id,
                    "nomer" => $ldSpec->nomer,
                );

            }
        }
        // endregion locker diskon

        $trreg = New MdlTransaksi();
        $trreg->setFilters(array());
        $trreg->setJointSelectFields("transaksi_id, main, items");
        $trreg->addFilter("transaksi_id in ('" . implode("','", $arrTrID_locker) . "')");
        $trregTmp = $trreg->lookupDataRegistries()->result();
//        showLast_query("biru");
//        arrPrint($trregTmp);
        if (sizeof($trregTmp) > 0) {
            foreach ($trregTmp as $regSpec) {
                $trid = $regSpec->transaksi_id;
                $main = blobDecode($regSpec->main);
                $items = blobDecode($regSpec->items);
//                arrPrint($main);
//                cekHere("[$trid]");
                $jenisTr = $main["jenisTr"];
                if ($jenisTr == NULL) {
                    $tr = New MdlTransaksi();
                    $tr->addFilter("id='$trid'");
                    $trTmp = $tr->lookupAll()->result();
                    $jenisTr = $trTmp[0]->jenis;
                }
                switch ($jenisTr) {
                    case "3344":
//                        foreach ($items as $pid => $pSpec){
////                            $arrDataDiskon[$trid] = "";
//                        }
//
                        break;
                    case "4643":
                        break;
                    case "467":
                        foreach ($items as $pid => $pSpec) {
                            foreach ($arrDiskonData as $diskon_id => $diskon_label) {
                                $new_key_id = "diskon_" . $diskon_id . "_id";
                                $new_key_nama = "diskon_" . $diskon_id . "_nama";
                                $new_key_nilai = "sub_diskon_" . $diskon_id . "_nilai";
                                $new_key_nilai_unit = "diskon_" . $diskon_id . "_nilai";
                                if ($pSpec[$new_key_nilai] > 0) {
                                    $arrDataDiskon[$trid][$pid][$diskon_id] = array(
                                        "supplier_id" => $pSpec["supplierID"],
                                        "supplier_nama" => $pSpec["supplierName"],
                                        "produk_id" => $pSpec[$new_key_id],
                                        "produk_nama" => $pSpec[$new_key_nama],
                                        "extern_id" => $pSpec[$new_key_id],
                                        "extern_nama" => $pSpec[$new_key_nama],
                                        "extern2_id" => $pSpec["id"],
                                        "extern2_nama" => $pSpec["nama"],
                                        "nilai" => $pSpec[$new_key_nilai],
                                        "nilai_unit" => $pSpec[$new_key_nilai_unit],
                                        "jumlah" => $pSpec["qty"],
                                    );
                                }
                            }
                        }
                        break;
                }
            }
        }
//        arrPrintCyan($arrDataDiskon);
//        mati_disini(__LINE__);

        $this->db->trans_start();


        if (sizeof($arrData) > 0) {
            foreach ($arrData as $trid => $data) {
                $where = array(
                    "id" => $trid,
                );
                $tr = New MdlTransaksi();
                $tr->setFilters(array());
                $tr->updateData($where, $data);
//                showLast_query("orange");
            }
        }

        if (sizeof($arrDataDiskon) > 0) {
            foreach ($arrDataDiskon as $trid => $tSpec) {
                foreach ($tSpec as $pid => $pSpec) {
                    foreach ($pSpec as $dkid => $dSpec) {
                        $diskon_nilai = $dSpec["nilai"];
                        $diskon_nilai_unit = $dSpec["nilai_unit"];
                        $diskon_nilai_jml = $dSpec["jumlah"];
//                        $diskon_nilai_locker = isset($arrTrData_locker[$trid][$pid][$dkid]["nilai"]) ? $arrTrData_locker[$trid][$pid][$dkid]["nilai"] : 0;
                        $diskon_nilai_locker_unit = isset($arrTrData_locker[$trid][$pid][$dkid]["nilai_unit"]) ? $arrTrData_locker[$trid][$pid][$dkid]["nilai_unit"] : 0;
                        $diskon_nilai_locker_jml = isset($arrTrData_locker[$trid][$pid][$dkid]["jumlah"]) ? $arrTrData_locker[$trid][$pid][$dkid]["jumlah"] : 0;
                        $diskon_nilai_locker = $diskon_nilai_locker_unit * $diskon_nilai_locker_jml;
                        $selisih_locker = $diskon_nilai_locker - $diskon_nilai;
                        if ($selisih_locker > 100) {
                            cekMerah("[diskon_nilai: $diskon_nilai] [diskon_nilai_locker: $diskon_nilai_locker]");
                            $where = array(
                                "id" => $arrTrData_locker[$trid][$pid][$dkid]["id_tbl"],
                            );
                            $data = array(
                                "nilai" => $diskon_nilai,
                                "nilai_unit" => $diskon_nilai,
                            );
                            $ld = New MdlLockerStockDiskonVendor();
                            $ld->setFilters(array());
                            $ld->updateData($where, $data);
                            showLast_query("orange");
                        }
                    }
                }
            }
        }

        if (sizeof($arrTrData_locker) > 0) {
            foreach ($arrTrData_locker as $trid => $tSpec) {
                foreach ($tSpec as $pid => $pSpec) {
                    foreach ($pSpec as $dkid => $dSpec) {
                        $id_tbl = $dSpec["id_tbl"];
                        $nomer = $dSpec["nomer"];
                        $nomer_ex = explode(".", $nomer);
                        switch ($nomer_ex[0]) {
                            case "467":
                                if (isset($arrDataDiskon[$trid])) {
                                    if (!isset($arrDataDiskon[$trid][$pid][$dkid])) {
                                        $where = array(
                                            "id" => $id_tbl,
                                        );
                                        $data = array(
                                            "nilai" => 0,
                                            "nilai_unit" => 0,
                                        );
                                        $ld = New MdlLockerStockDiskonVendor();
                                        $ld->setFilters(array());
                                        $ld->updateData($where, $data);
                                        showLast_query("ungu");
                                    }
                                }
                                break;
                        }

                    }
                }
            }
        }

        mati_disini("---SETOP--- " . __LINE__);
        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");
        cekHijau("<h3>DONE...</h3>");

    }

    public function cekRebateSupplier()
    {
        $this->load->model("MdlTransaksi");
        $this->load->model("Mdls/MdlSupplierDiskon");
        $this->load->model("Mdls/MdlLockerStockDiskonVendor");
        $this->load->model("Coms/ComRekeningPembantuPiutangSupplierDetailTransItem");

        //-----
        $tbl_mutasi = "__rek_pembantu_piutangsupplier__1010020030";
        $supplier_id = "4";
        $date1 = "2024-01-01";
        $date2 = "2026-12-31";
        $arrHeader = array(
            "id" => "trid",
            "dtime" => "dtime",
            "referenceNomer__2" => "nomer po",
            "nomer" => "nomer grn",
            "suppliers_id" => "ID supplier",
            "suppliers_nama" => "supplier",
//            "nilai_diskonpo" => array(
//                "label" => "PO rebate",
//                "format" => "debet",
//            ),
//            "nilai_diskonpo_freeproduk" => array(
//                "label" => "PO rebate<br>(freeproduk)",
//                "format" => "debet",
//            ),
            "nilai" => array(
                "label" => "GRN rebate",
                "format" => "debet",
            ),
            "nilai_freeproduk" => array(
                "label" => "GRN rebate<br>(freeproduk)",
                "format" => "debet",
            ),
//            "nilai_grn_batal" => array(
//                "label" => "GRN rebate<br>(BATAL)",
//                "format" => "debet",
//            ),
            "nilai_piutang" => array(
                "label" => "diklaim",
                "format" => "debet",
            ),
//            "nilai_piutang_batal" => array(
//                "label" => "diklaim<br>(BATAL)",
//                "format" => "debet",
//            ),
            "belum_diklaim" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "belum diklaim",
                "format" => "debet",
            ),
            "rek_realisasi_klaim" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "rek_realisasi_klaim",
                "format" => "debet",
            ),
//            "selisih_plus" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "selisih plus",
//                "format" => "debet",
//            ),
            //-------
//            "nilai_persediaan" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "persediaan",
//                "format" => "debet",
//            ),
//            "nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "credit note",
//                "format" => "debet",
//            ),
//            "nilai_voucher" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "voucher",
//                "format" => "debet",
//            ),
//            "nilai_cash" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "kas",
//                "format" => "debet",
//            ),
//            "nilai_logam_mulia" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "logam mulia",
//                "format" => "debet",
//            ),
//            "nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "pph23<br>dibayar dimuka",
//                "format" => "debet",
//            ),
            //-------
//            "new_nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "credit note<br>REVISI",
//                "format" => "debet",
//            ),
//            "new_nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "pph23<br>dibayar dimuka<br>REVISI",
//                "format" => "debet",
//            ),
            //-------
//            "adj_nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "credit note<br>ADJ",
//                "format" => "debet",
//            ),
//            "adj_nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "pph23<br>dibayar dimuka<br>ADJ",
//                "format" => "debet",
//            ),
        );
        //region data diskon-----
        $ds = New MdlSupplierDiskon();
        $dsTmp = $ds->lookupAll()->result();
        foreach ($dsTmp as $dsSpec) {
            $arrDiskonData[$dsSpec->id] = $dsSpec->label;
        }
        //endregion-----

        // region transaksi grn 467
        $arrTrIDs = array();
        $jenisTr = array(
            "467",
            "4643",
            "3344",
        );
        $date = isset($_GET["date"]) ? $_GET["date"] : "2025-05";
        $date_ex = explode("-", $date);
        $month = isset($date_ex[1]) ? $date_ex[1] : date("m");
        $year = isset($date_ex[0]) ? $date_ex[0] : date("Y");
        $tr = New MdlTransaksi();
//        $tr->addFilter("jenis='$jenisTr'");
        if($supplier_id > 0){
            $tr->addFilter("suppliers_id='$supplier_id'");
        }
        $tr->addFilter("jenis in ('" . implode("','", $jenisTr) . "')");
//        $tr->addFilter("month(dtime)='$month'");
//        $tr->addFilter("year(dtime)='$year'");
        $tr->addFilter("date(dtime)>='$date1'");
        $tr->addFilter("date(dtime)<='$date2'");
        $trTmp = $tr->lookupAll()->result();
        if (sizeof($trTmp) > 0) {
            foreach ($trTmp as $trSpec) {
                $trid = $trSpec->id;// id GRN
                $trash_4 = $trSpec->trash_4;
                $jenis = $trSpec->jenis;
                $idsHis = ($trSpec->ids_his != null) ? blobDecode($trSpec->ids_his) : array();
                if (sizeof($idsHis) > 0) {
                    foreach ($idsHis as $step_his => $data_his) {
                        if ($step_his == 1) {
                            $subCounters = blobDecode($data_his["counters"]);
                            $countStepCode = 0;
                            foreach ($subCounters["stepCode"] as $cc => $cct) {
                                $countStepCode = $cct;
                            }
                            $arrTransaksi[$trid]['referenceID'] = $data_his["trID"];
                            $arrTransaksi[$trid]['referenceNumber'] = $data_his["nomer"];
                            $arrTransaksi[$trid]['referenceNomer'] = $data_his["nomer"];
                            $arrTransaksi[$trid]['referenceDtime'] = $data_his["dtime"];
                            $arrTransaksi[$trid]['referenceFulldate'] = $data_his["fulldate"];
                            $arrTransaksi[$trid]['referenceCount'] = $countStepCode;
                        }
                        $arrTransaksi[$trid]['referenceID__' . $step_his] = $data_his["trID"];
                        $arrTransaksi[$trid]['referenceNumber__' . $step_his] = $data_his["nomer"];
                        $arrTransaksi[$trid]['referenceNomer__' . $step_his] = $data_his["nomer"];
                        $arrTransaksi[$trid]['referenceDtime__' . $step_his] = $data_his["dtime"];
                        $arrTransaksi[$trid]['referenceFulldate__' . $step_his] = $data_his["fulldate"];
                    }
                }

                $arrTrIDs[$trid] = $trid;
                foreach ($arrHeader as $key => $val) {
                    if (!isset($arrTransaksi[$trid][$key])) {
                        $arrTransaksi[$trid][$key] = isset($trSpec->$key) ? $trSpec->$key : "";
                    }
                }
                $trreg = New MdlTransaksi();
                $trreg->setFilters(array());
                $trreg->setJointSelectFields("transaksi_id, items, items2_sum, main");
                $trreg->addFilter("transaksi_id='$trid'");
                $tmpReg = $trreg->lookupDataRegistries()->result();
                $items = blobDecode($tmpReg[0]->items);
                $items2_sum = blobDecode($tmpReg[0]->items2_sum);
                $main = blobDecode($tmpReg[0]->main);
                switch ($jenis) {
                    case "467":
                        foreach ($items as $pid => $pSpec) {
                            foreach ($arrDiskonData as $diskon_id => $diskon_label) {
                                $new_key_id = "diskon_" . $diskon_id . "_id";
                                $new_key_nilai = "sub_diskon_" . $diskon_id . "_nilai";

                                $arrDataLocker[$trid][$diskon_id]["id"] = isset($pSpec[$new_key_id]) ? $pSpec[$new_key_id] : 0;
                                if (!isset($arrDataLocker[$trid][$diskon_id]["nilai"])) {
                                    $arrDataLocker[$trid][$diskon_id]["nilai"] = 0;
                                }
                                $arrDataLocker[$trid][$diskon_id]["nilai"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;
                                if (!isset($arrDataLockerTotal[$trid]["nilai"])) {
                                    $arrDataLockerTotal[$trid]["nilai"] = 0;
                                }
                                $arrDataLockerTotal[$trid]["nilai"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;

                                if ($trash_4 == 1) {
                                    $arrDataLockerBatal[$trid][$diskon_id]["id"] = isset($pSpec[$new_key_id]) ? $pSpec[$new_key_id] : 0;
                                    if (!isset($arrDataLockerBatal[$trid][$diskon_id]["nilai_grn_batal"])) {
                                        $arrDataLockerBatal[$trid][$diskon_id]["nilai_grn_batal"] = 0;
                                    }
                                    $arrDataLockerBatal[$trid][$diskon_id]["nilai_grn_batal"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;
                                    if (!isset($arrDataLockerTotalBatal[$trid]["nilai_grn_batal"])) {
                                        $arrDataLockerTotalBatal[$trid]["nilai_grn_batal"] = 0;
                                    }
                                    $arrDataLockerTotalBatal[$trid]["nilai_grn_batal"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;
//                            cekMerah("MASUK DISINI... [$trid]");
                                }

                            }

                        }
                        break;
                    case "4643":
                        $arrTransaksi[$trid]["referenceID__2"] = $main["referensi_so"];
                        $arrTransaksi[$trid]["referenceNomer__2"] = $main["referensi_so__nomer"];
                        foreach ($items2_sum as $pid => $pSpec) {
                            foreach ($arrDiskonData as $diskon_id => $diskon_label) {
                                if ($pSpec["diskon_id"] == $diskon_id) {
                                    $new_key_id = "diskon_id";
                                    $new_key_nilai = "sub_diskon_nilai";

                                    $arrDataLocker[$trid][$diskon_id]["id"] = isset($pSpec[$new_key_id]) ? $pSpec[$new_key_id] : 0;
                                    if (!isset($arrDataLocker[$trid][$diskon_id]["nilai"])) {
                                        $arrDataLocker[$trid][$diskon_id]["nilai"] = 0;
                                    }
                                    $arrDataLocker[$trid][$diskon_id]["nilai"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;
                                    if (!isset($arrDataLockerTotal[$trid]["nilai"])) {
                                        $arrDataLockerTotal[$trid]["nilai"] = 0;
                                    }
                                    $arrDataLockerTotal[$trid]["nilai"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;

                                    if ($trash_4 == 1) {
                                        $arrDataLockerBatal[$trid][$diskon_id]["id"] = isset($pSpec[$new_key_id]) ? $pSpec[$new_key_id] : 0;
                                        if (!isset($arrDataLockerBatal[$trid][$diskon_id]["nilai_grn_batal"])) {
                                            $arrDataLockerBatal[$trid][$diskon_id]["nilai_grn_batal"] = 0;
                                        }
                                        $arrDataLockerBatal[$trid][$diskon_id]["nilai_grn_batal"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;
                                        if (!isset($arrDataLockerTotalBatal[$trid]["nilai_grn_batal"])) {
                                            $arrDataLockerTotalBatal[$trid]["nilai_grn_batal"] = 0;
                                        }
                                        $arrDataLockerTotalBatal[$trid]["nilai_grn_batal"] += isset($pSpec[$new_key_nilai]) ? $pSpec[$new_key_nilai] : 0;

                                    }
                                }

                            }

                        }
                        break;
                    case "3344":
                        $arrDataLockerTotal[$trid]["nilai"] = isset($main["nilai_piutang"]) ? $main["nilai_piutang"] : 0;
                        break;
                }


                $arrTransaksi[$trid]["nilai_freeproduk"] = isset($main["produk_rel_harga"]) ? $main["produk_rel_harga"] : 0;
//                break;

                $tridpo = $arrTransaksi[$trid]['referenceID__2'];
                $trreg = New MdlTransaksi();
                $trreg->setFilters(array());
                $trreg->setJointSelectFields("transaksi_id, items, items2_sum, main");
                $trreg->addFilter("transaksi_id='$tridpo'");
                $tmpReg = $trreg->lookupDataRegistries()->result();
                $mainpo = blobDecode($tmpReg[0]->main);
//                $arrTransaksi[$trid]["nilai_diskonpo"] = isset($mainpo["diskon_nilai"]) ? $mainpo["diskon_nilai"] : 0;
                $arrTransaksi[$trid]["nilai_diskonpo"] = isset($mainpo["diskon_nilai_total"]) ? $mainpo["diskon_nilai_total"] : 0;
                $arrTransaksi[$trid]["nilai_diskonpo_freeproduk"] = isset($mainpo["produk_rel_harga"]) ? $mainpo["produk_rel_harga"] : 0;

            }
        }
        // endregion transaksi grn 467


        // region transaksi klaim 3333
        $arrIniTridKlaim = array();
        $arrIniTridYangDiKlaim = array();
        $arrKlaim = array();
        $arrKlaimBatal = array();
        $tr = New MdlTransaksi();
        $tr->addFilter("jenis='3333'");
        if($supplier_id > 0){
            $tr->addFilter("suppliers_id='$supplier_id'");
        }
        $tr->addFilter("reference_id in ('" . implode("','", $arrTrIDs) . "')");
        $trTmp = $tr->lookupAll()->result();
        if (sizeof($trTmp) > 0) {
            foreach ($trTmp as $trSpec) {
                $jenis = $trSpec->jenis;
                $ini_trid = $trSpec->id;
                $trash_4 = $trSpec->trash_4;

                $reference_id = $trSpec->reference_id;
                $trreg = New MdlTransaksi();
                $trreg->setFilters(array());
                $trreg->setJointSelectFields("transaksi_id, main");
                $trreg->addFilter("transaksi_id='$ini_trid'");
                $tmpReg = $trreg->lookupDataRegistries()->result();
                $main = blobDecode($tmpReg[0]->main);
                $arrKlaim[$reference_id] = array(
                    "nilai_persediaan" => $main["nilai_persediaan"],
                    "nilai_piutang" => $main["nilai_piutang"],
                    "nilai_credit_note" => $main["nilai_credit_note"],
                    "nilai_voucher" => $main["nilai_voucher"],
                    "nilai_cash" => $main["nilai_cash"],
                    "nilai_logam_mulia" => $main["nilai_logam_mulia"],
                    "nilai_pph23" => $main["nilai_pph23"],
                );
                if ($trash_4 == 1) {
                    $arrKlaimBatal[$reference_id] = array(
                        "nilai_persediaan_batal" => $main["nilai_persediaan"],
                        "nilai_piutang_batal" => $main["nilai_piutang"],
                        "nilai_credit_note_batal" => $main["nilai_credit_note"],
                        "nilai_voucher_batal" => $main["nilai_voucher"],
                        "nilai_cash_batal" => $main["nilai_cash"],
                        "nilai_logam_mulia_batal" => $main["nilai_logam_mulia"],
                        "nilai_pph23_batal" => $main["nilai_pph23"],
                    );
                }

                $arrIniTridKlaim[] = $ini_trid;// 3333
                $arrIniTridYangDiKlaim[$reference_id] = $ini_trid;
            }

            if(sizeof($arrIniTridKlaim) > 0){
                $this->db->from($tbl_mutasi);
                $this->db->where_in("transaksi_id", $arrIniTridKlaim);
                $query = $this->db->get()->result();
//                showLast_query("biru");
                foreach ($query as $qspec){
                    if(!isset($realisasiKlaim[$qspec->transaksi_id])){
                        $realisasiKlaim[$qspec->transaksi_id] = 0;
                    }
                    $realisasiKlaim[$qspec->transaksi_id] += $qspec->kredit;
                }
            }
//arrPrint($realisasiKlaim);
        }
        // endregion transaksi klaim 3333


        $this->db->trans_start();

        $arrSupplierCek = array();

        $str = "<table style='border:1px solid black;width:100%;' rules='all'>";

        $str .= "<tr>";
        $str .= "<th>no.</th>";
        foreach ($arrHeader as $key => $val) {
            if (is_array($val)) {
                $str .= "<th>" . $val["label"] . "</th>";
            }
            else {
                $str .= "<th>$val</th>";
            }
        }
        $str .= "</tr>";

        if (sizeof($arrTransaksi) > 0) {
            $no = 0;
            foreach ($arrTransaksi as $trid => $trSpec) {
                $supplier_id = $trSpec["suppliers_id"];
                $supplier_nama = $trSpec["suppliers_nama"];
                $bgcolor = "";

                //----rebate dari items grn
                if (isset($arrDataLockerTotal[$trid])) {
                    foreach ($arrDataLockerTotal[$trid] as $aa => $bb) {
                        $trSpec[$aa] = $bb;
                    }
                }
                //----rebate dari items grn
                if (isset($arrDataLockerTotalBatal[$trid])) {
                    $bgcolor = "#ff66ff";
                    foreach ($arrDataLockerTotalBatal[$trid] as $aa => $bb) {
                        $trSpec[$aa] = $bb;
                    }
                }
                //----rebate klaim
                if (isset($arrKlaim[$trid])) {
                    foreach ($arrKlaim[$trid] as $cc => $dd) {
                        $trSpec[$cc] = $dd;
                    }
                }
                //----rebate klaim
                if (isset($arrKlaimBatal[$trid])) {
                    foreach ($arrKlaimBatal[$trid] as $cc => $dd) {
                        $trSpec[$cc] = $dd;
                    }
                }

                $selisih = $trSpec["nilai_piutang"] - ($trSpec["nilai"] + $trSpec["nilai_freeproduk"]);
                $trSpec["selisih_plus"] = ($selisih > 0) ? $selisih : 0;
                if ($trSpec["selisih_plus"] > 10) {
                    $bgcolor = "#ff3300";
                    //---- nilai klaim seharusnya (creditnote, pph23 dibayar dimuka, voucher, kas, logam mulia)
                    if ($trSpec["nilai_pph23"] > 10) {
                        $new_nilai_pph23 = (15 / 100) * $trSpec["nilai"];
                        $new_nilai_credit_note = (85 / 100) * $trSpec["nilai"];
                        $trSpec["new_nilai_pph23"] = $new_nilai_pph23;
                        $trSpec["new_nilai_credit_note"] = $new_nilai_credit_note;

                        $adj_nilai_pph23 = (15 / 100) * $trSpec["selisih_plus"];
                        $adj_nilai_credit_note = (85 / 100) * $trSpec["selisih_plus"];
                        $trSpec["adj_nilai_pph23"] = $adj_nilai_pph23;
                        $trSpec["adj_nilai_credit_note"] = $adj_nilai_credit_note;
                        //------PER SUPPLIER
                        $arrSupplierCek[$supplier_id]["suppliers_id"] = $supplier_id;
                        $arrSupplierCek[$supplier_id]["suppliers_nama"] = $supplier_nama;
                        if (!isset($arrSupplierCek[$supplier_id]["new_nilai_pph23"])) {
                            $arrSupplierCek[$supplier_id]["new_nilai_pph23"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["new_nilai_pph23"] += $new_nilai_pph23;

                        if (!isset($arrSupplierCek[$supplier_id]["new_nilai_credit_note"])) {
                            $arrSupplierCek[$supplier_id]["new_nilai_credit_note"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["new_nilai_credit_note"] += $new_nilai_credit_note;

                        if (!isset($arrSupplierCek[$supplier_id]["adj_nilai_pph23"])) {
                            $arrSupplierCek[$supplier_id]["adj_nilai_pph23"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["adj_nilai_pph23"] += $adj_nilai_pph23;

                        if (!isset($arrSupplierCek[$supplier_id]["adj_nilai_credit_note"])) {
                            $arrSupplierCek[$supplier_id]["adj_nilai_credit_note"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["adj_nilai_credit_note"] += $adj_nilai_credit_note;
                        //------
                    }
                }

                // belum diklaim belum_diklaim
                $belum_diklaim = ($trSpec["nilai"] + $trSpec["nilai_freeproduk"]) - $trSpec["nilai_piutang"];
                $belum_diklaim = ($belum_diklaim > 0) ? $belum_diklaim : 0;
                $trSpec["belum_diklaim"] = $belum_diklaim;

                // rekening realiasi klaim
                $trid__ = $arrIniTridYangDiKlaim[$trid];
                $trSpec["rek_realisasi_klaim"] = $realisasiKlaim[$trid__];


                $no++;
                $str .= "<tr style='background-color:$bgcolor;'>";
                $str .= "<td>$no</td>";
                foreach ($arrHeader as $key => $val) {
                    $val_data = isset($trSpec[$key]) ? $trSpec[$key] : "";
                    if (is_array($val)) {
                        $val_data_f = formatField_he_format($val["format"], $val_data);
                        $align = "right";
                    }
                    else {
                        $val_data_f = $val_data;
                        $align = "";
                    }
                    $str .= "<td style='text-align:$align;'>";
                    $str .= $val_data_f;
                    $str .= "</td>";

                    if (is_numeric($val_data)) {
                        if (!isset($totalBawah[$key])) {
                            $totalBawah[$key] = 0;
                        }
                        $totalBawah[$key] += $val_data;
                    }
                }
                $str .= "</tr>";
            }
        }

        $str .= "<tr>";
        $str .= "<th>-</th>";
        foreach ($arrHeader as $key => $val) {
            $val_data = isset($totalBawah[$key]) ? $totalBawah[$key] : "";
            if (is_array($val)) {
                $val_data_f = formatField_he_format($val["format"], $val_data);
                $align = "right";
            }
            else {
                $val_data_f = $val_data;
                $align = "";
            }
            $str .= "<th style='text-align:$align;'>";
            $str .= $val_data_f;
            $str .= "</th>";
        }
        $str .= "</tr>";

        $str .= "<tr>";
        $str .= "<th>-</th>";
        foreach ($arrHeader as $key => $val) {
            if (is_array($val)) {
                $val_data_f = $val["label"];
                $align = "right";
            }
            else {
                $val_data_f = $val;
                $align = "";
            }
            $str .= "<th style='text-align:$align;'>";
            $str .= $val_data_f;
            $str .= "</th>";
        }
        $str .= "</tr>";

        $str .= "</table>";
        //-------
        $str .= "<br><br><br>";
        //-------

//        arrPrintCyan($arrSupplierCek);

        echo $str;
        mati_disini("---SETOP--- " . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");


    }

    public function cekKlaimRebateSupplier()
    {
        $this->load->model("MdlTransaksi");
        $this->load->model("Mdls/MdlSupplierDiskon");
        $this->load->model("Mdls/MdlLockerStockDiskonVendor");
        $this->load->model("Coms/ComRekeningPembantuPiutangSupplierDetailTransItem");
        $this->load->model("Coms/ComJurnal");
        //-----
        $arrTrIDs = array();
        $jenisTr = array(
            "3333",
        );
        $date = isset($_GET["date"]) ? $_GET["date"] : "2025-05";
        $date_ex = explode("-", $date);
        $month = isset($date_ex[1]) ? $date_ex[1] : date("m");
        $year = isset($date_ex[0]) ? $date_ex[0] : date("Y");
        $arrHeader = array(
            "dtime" => "dtime",
            "id" => "trid",
            "nomer" => "nomer<br>klaim",
            "reference_id" => "ID referensi",
            "reference_nomer" => "nomer<br>referensi",
            "suppliers_id" => "ID supplier",
            "suppliers_nama" => "supplier",
//            "nilai_grn_batal" => array(
//                "label" => "rebate<br>(BATAL)",
//                "format" => "debet",
//            ),
            "nilai_piutang" => array(
                "label" => "diklaim",
                "format" => "debet",
            ),
            "nilai_piutang_batal" => array(
                "label" => "diklaim<br>(BATAL)",
                "format" => "debet",
            ),
            //-------
            "nilai_persediaan" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "persediaan",
                "format" => "debet",
            ),
            "nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "credit note",
                "format" => "debet",
            ),
            "nilai_voucher" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "voucher",
                "format" => "debet",
            ),
            "nilai_cash" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "kas",
                "format" => "debet",
            ),
            "nilai_logam_mulia" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "logam<br>mulia",
                "format" => "debet",
            ),
            "nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "pph23<br>dibayar dimuka",
                "format" => "debet",
            ),
            //-------
            "nilai_diskon_reguler" => array(
                "label" => "rebate<br>reguler",
                "format" => "debet",
            ),
            "nilai_freeproduk" => array(
                "label" => "rebate<br>(freeproduk)",
                "format" => "debet",
            ),
            "nilai_rebate_total" => array(
                "label" => "rebate<br>(total)",
                "format" => "debet",
            ),
            "selisih_plus" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "selisih plus<br>(lebih klaim)",
                "format" => "debet",
                "bgcolor" => "yellow",
            ),
//            "new_nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "credit note<br>REVISI",
//                "format" => "debet",
//            ),
//            "new_nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "pph23<br>dibayar dimuka<br>REVISI",
//                "format" => "debet",
//            ),
            //-------
//            "adj_nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "credit note<br>ADJ",
//                "format" => "debet",
//            ),
//            "adj_nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "pph23<br>dibayar dimuka<br>ADJ",
//                "format" => "debet",
//            ),
        );
        $arrHeader2 = array(
//            "dtime" => "dtime",
//            "id" => "trid",
//            "nomer" => "nomer<br>klaim",
//            "reference_id" => "ID referensi",
//            "reference_nomer" => "nomer<br>referensi",
            "suppliers_id" => "ID supplier",
            "suppliers_nama" => "supplier",
            //-------
//            "nilai_piutang" => array(
//                "label" => "diklaim",
//                "format" => "debet",
//            ),
//            "nilai_piutang_batal" => array(
//                "label" => "diklaim<br>(BATAL)",
//                "format" => "debet",
//            ),
//            //-------
//            "nilai_persediaan" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "persediaan",
//                "format" => "debet",
//            ),
//            "nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "credit note",
//                "format" => "debet",
//            ),
//            "nilai_voucher" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "voucher",
//                "format" => "debet",
//            ),
//            "nilai_cash" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "kas",
//                "format" => "debet",
//            ),
//            "nilai_logam_mulia" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "logam<br>mulia",
//                "format" => "debet",
//            ),
//            "nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
//                "label" => "pph23<br>dibayar dimuka",
//                "format" => "debet",
//            ),
//            //-------
//            "nilai_diskon_reguler" => array(
//                "label" => "rebate<br>reguler",
//                "format" => "debet",
//            ),
//            "nilai_freeproduk" => array(
//                "label" => "rebate<br>(freeproduk)",
//                "format" => "debet",
//            ),
//            "nilai_rebate_total" => array(
//                "label" => "rebate<br>(total)",
//                "format" => "debet",
//            ),
            "selisih_plus" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "selisih plus<br>(lebih klaim)",
                "format" => "debet",
            ),
            //-------
            "new_nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "credit note<br>REVISI",
                "format" => "debet",
            ),
            "new_nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "pph23<br>dibayar dimuka<br>REVISI",
                "format" => "debet",
            ),
            //-------
            "adj_nilai_credit_note" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "credit note<br>ADJ",
                "format" => "debet",
            ),
            "adj_nilai_pph23" => array(// klaim lebih besar dari diskon yang didapat
                "label" => "pph23<br>dibayar dimuka<br>ADJ",
                "format" => "debet",
            ),
        );

        //region data diskon-----
        $ds = New MdlSupplierDiskon();
        $dsTmp = $ds->lookupAll()->result();
        foreach ($dsTmp as $dsSpec) {
            $arrDiskonData[$dsSpec->id] = $dsSpec->label;
        }
        //endregion-----

        // region transaksi klaim 3333
        $arrKlaim = array();
        $arrKlaimBatal = array();
        $tr = New MdlTransaksi();
        $tr->addFilter("jenis='3333'");
//        $tr->addFilter("month(dtime)='$month'");
//        $tr->addFilter("year(dtime)='$year'");
        $tr->addFilter("date(dtime)>='$date'");
//        $tr->addFilter("date(dtime)<='2024-12-31'");
        $tr->addFilter("jenis in ('" . implode("','", $jenisTr) . "')");
//        $tr->addFilter("id='200161'");
        $trTmp = $tr->lookupAll()->result();
        if (sizeof($trTmp) > 0) {
            foreach ($trTmp as $trSpec) {
                $jenis = $trSpec->jenis;
                $ini_trid = $trSpec->id;
                $trash_4 = $trSpec->trash_4;

                $reference_id = $trSpec->reference_id;
                $reference_nomer = $trSpec->reference_nomer;
                $reference_jenis = $trSpec->reference_jenis;
                $trreg = New MdlTransaksi();
                $trreg->setFilters(array());
                $trreg->setJointSelectFields("transaksi_id, main");
                $trreg->addFilter("transaksi_id='$ini_trid'");
                $tmpReg = $trreg->lookupDataRegistries()->result();
                $main = blobDecode($tmpReg[0]->main);
                $arrKlaim[$ini_trid] = array(
                    "id" => $trSpec->id,
                    "dtime" => $trSpec->dtime,
                    "nomer" => $trSpec->nomer,
                    "suppliers_id" => $trSpec->suppliers_id,
                    "suppliers_nama" => $trSpec->suppliers_nama,
                    "reference_id" => $reference_id,
                    "reference_nomer" => $reference_nomer,

                    "nilai_persediaan" => $main["nilai_persediaan"],
                    "nilai_piutang" => $main["nilai_piutang"],
                    "nilai_credit_note" => $main["nilai_credit_note"],
                    "nilai_voucher" => $main["nilai_voucher"],
                    "nilai_cash" => $main["nilai_cash"],
                    "nilai_logam_mulia" => $main["nilai_logam_mulia"],
                    "nilai_pph23" => $main["nilai_pph23"],
                );
                if ($trash_4 == 1) {
                    $arrKlaimBatal[$ini_trid] = array(
                        "nilai_persediaan_batal" => $main["nilai_persediaan"],
                        "nilai_piutang_batal" => $main["nilai_piutang"],
                        "nilai_credit_note_batal" => $main["nilai_credit_note"],
                        "nilai_voucher_batal" => $main["nilai_voucher"],
                        "nilai_cash_batal" => $main["nilai_cash"],
                        "nilai_logam_mulia_batal" => $main["nilai_logam_mulia"],
                        "nilai_pph23_batal" => $main["nilai_pph23"],
                    );
                }

                $trregref = New MdlTransaksi();
                $trregref->setFilters(array());
                $trregref->setJointSelectFields("transaksi_id, main, items");
                $trregref->addFilter("transaksi_id='$reference_id'");
                $tmpRegRef = $trregref->lookupDataRegistries()->result();
                switch ($reference_jenis) {
                    case "467":
                        $main = blobDecode($tmpRegRef[0]->main);
                        $diskon_nilai = isset($main["diskon_nilai_total"]) ? $main["diskon_nilai_total"] : 0;
                        if ($diskon_nilai == 0) {
                            $cj = New ComJurnal();
                            $cj->addFilter("transaksi_id='$reference_id'");
                            $cj->addFilter("rekening='1010020030'");
                            $cjTmp = $cj->lookupAll()->result();
                            $diskon_nilai = $cjTmp[0]->debet;
                        }
//                        mati_disini(__LINE__);
                        $arrKlaim[$ini_trid]["nilai_diskon_reguler"] = $diskon_nilai;
                        $arrKlaim[$ini_trid]["nilai_freeproduk"] = $main["produk_rel_harga"];
                        break;
                    case "3344":
                        $main = blobDecode($tmpRegRef[0]->main);
                        $arrKlaim[$ini_trid]["nilai_diskon_reguler"] = $main["nilai_piutang"];
                        break;
                    case "4643":
                        $main = blobDecode($tmpRegRef[0]->main);
                        $arrKlaim[$ini_trid]["nilai_diskon_reguler"] = $main["diskon_nilai"];
                        break;
                }
            }
        }
        // endregion transaksi klaim 3333

        $this->db->trans_start();

        $str = "<table style='border:1px solid black;width:100%;' rules='all'>";

        $str .= "<tr>";
        $str .= "<th>no.</th>";
        foreach ($arrHeader as $key => $val) {
            if (is_array($val)) {
                $bgcolor = isset($val["bgcolor"]) ? $val["bgcolor"] : "";
                $str .= "<th style='background-color:$bgcolor;'>" . $val["label"] . "</th>";
            }
            else {
                $str .= "<th>$val</th>";
            }
        }
        $str .= "</tr>";

        if (sizeof($arrKlaim) > 0) {
            $no = 0;
            foreach ($arrKlaim as $trid => $trSpec) {
                $supplier_id = $trSpec["suppliers_id"];
                $supplier_nama = $trSpec["suppliers_nama"];
                $bgcolor = "";

                //----rebate dari items grn
                if (isset($arrDataLockerTotal[$trid])) {
                    foreach ($arrDataLockerTotal[$trid] as $aa => $bb) {
                        $trSpec[$aa] = $bb;
                    }
                }
                //----rebate dari items grn
                if (isset($arrDataLockerTotalBatal[$trid])) {
                    $bgcolor = "#ff66ff";
                    foreach ($arrDataLockerTotalBatal[$trid] as $aa => $bb) {
                        $trSpec[$aa] = $bb;
                    }
                }
                //----rebate klaim
//                if (isset($arrKlaim[$trid])) {
//                    foreach ($arrKlaim[$trid] as $cc => $dd) {
//                        $trSpec[$cc] = $dd;
//                    }
//                }
                //----rebate klaim
                if (isset($arrKlaimBatal[$trid])) {
                    foreach ($arrKlaimBatal[$trid] as $cc => $dd) {
                        $trSpec[$cc] = $dd;
                    }
                }

                $trSpec["nilai_rebate_total"] = $trSpec["nilai_diskon_reguler"] + $trSpec["nilai_freeproduk"];

                $selisih = $trSpec["nilai_piutang"] - $trSpec["nilai_rebate_total"];
                $trSpec["selisih_plus"] = ($selisih > 0) ? $selisih : 0;
                if ($trSpec["selisih_plus"] > 10) {
                    $bgcolor = "#ff0000";
                    //---- nilai klaim seharusnya (creditnote, pph23 dibayar dimuka, voucher, kas, logam mulia)
                    if ($trSpec["nilai_pph23"] > 10) {
                        $new_nilai_pph23 = (15 / 100) * $trSpec["nilai_rebate_total"];
                        $new_nilai_credit_note = (85 / 100) * $trSpec["nilai_rebate_total"];
                        $trSpec["new_nilai_pph23"] = $new_nilai_pph23;
                        $trSpec["new_nilai_credit_note"] = $new_nilai_credit_note;

                        $adj_nilai_pph23 = (15 / 100) * $trSpec["selisih_plus"];
                        $adj_nilai_credit_note = (85 / 100) * $trSpec["selisih_plus"];
                        $trSpec["adj_nilai_pph23"] = $adj_nilai_pph23;
                        $trSpec["adj_nilai_credit_note"] = $adj_nilai_credit_note;

                        //------PER SUPPLIER
                        $arrSupplierCek[$supplier_id]["suppliers_id"] = $supplier_id;
                        $arrSupplierCek[$supplier_id]["suppliers_nama"] = $supplier_nama;

                        if (!isset($arrSupplierCek[$supplier_id]["selisih_plus"])) {
                            $arrSupplierCek[$supplier_id]["selisih_plus"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["selisih_plus"] += $trSpec["selisih_plus"];

                        if (!isset($arrSupplierCek[$supplier_id]["new_nilai_pph23"])) {
                            $arrSupplierCek[$supplier_id]["new_nilai_pph23"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["new_nilai_pph23"] += $new_nilai_pph23;

                        if (!isset($arrSupplierCek[$supplier_id]["new_nilai_credit_note"])) {
                            $arrSupplierCek[$supplier_id]["new_nilai_credit_note"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["new_nilai_credit_note"] += $new_nilai_credit_note;

                        if (!isset($arrSupplierCek[$supplier_id]["adj_nilai_pph23"])) {
                            $arrSupplierCek[$supplier_id]["adj_nilai_pph23"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["adj_nilai_pph23"] += $adj_nilai_pph23;

                        if (!isset($arrSupplierCek[$supplier_id]["adj_nilai_credit_note"])) {
                            $arrSupplierCek[$supplier_id]["adj_nilai_credit_note"] = 0;
                        }
                        $arrSupplierCek[$supplier_id]["adj_nilai_credit_note"] += $adj_nilai_credit_note;
                        //------
                    }
                }

                $no++;
                $str .= "<tr style='background-color:$bgcolor;'>";
                $str .= "<td>$no</td>";
                foreach ($arrHeader as $key => $val) {
                    $val_data = isset($trSpec[$key]) ? $trSpec[$key] : "";
                    if (is_array($val)) {
                        $val_data_f = formatField_he_format($val["format"], $val_data);
                        $align = "right";
                        $bgcolor = isset($val["bgcolor"]) ? $val["bgcolor"] : "";
                    }
                    else {
                        $val_data_f = $val_data;
                        $align = "";
                        $bgcolor = "";
                    }
                    $str .= "<td style='text-align:$align;background-color:$bgcolor;'>";
                    $str .= $val_data_f;
                    $str .= "</td>";

                    if (is_numeric($val_data)) {
                        if (!isset($totalBawah[$key])) {
                            $totalBawah[$key] = 0;
                        }
                        $totalBawah[$key] += $val_data;
                    }
                }
                $str .= "</tr>";
            }
        }

        $str .= "<tr>";
        $str .= "<th>-</th>";
        foreach ($arrHeader as $key => $val) {
            $val_data = isset($totalBawah[$key]) ? $totalBawah[$key] : "";
            if (is_array($val)) {
                $bgcolor = isset($val["bgcolor"]) ? $val["bgcolor"] : "";
                $val_data_f = formatField_he_format($val["format"], $val_data);
                $align = "right";
            }
            else {
                $bgcolor = "";
                $val_data_f = $val_data;
                $align = "";
            }
            $str .= "<th style='text-align:$align;background-color:$bgcolor;'>";
            $str .= $val_data_f;
            $str .= "</th>";
        }
        $str .= "</tr>";

        $str .= "<tr>";
        $str .= "<th>-</th>";
        foreach ($arrHeader as $key => $val) {
            if (is_array($val)) {
                $val_data_f = $val["label"];
                $align = "right";
            }
            else {
                $val_data_f = $val;
                $align = "";
            }
            $str .= "<th sstyle='text-align:$align;'>";
            $str .= $val_data_f;
            $str .= "</th>";
        }
        $str .= "</tr>";

        $str .= "</table>";
        //-------
        $str .= "<br><br><br>";
        //-------
        echo $str;
//        arrPrintHitam($arrSupplierCek);

        $str = "<table style='border:1px solid black;width:100%;' rules='all'>";
        $str .= "<tr>";
        $str .= "<th>no.</th>";
        foreach ($arrHeader2 as $key => $val) {
            if (is_array($val)) {
                $str .= "<th>" . $val["label"] . "</th>";
            }
            else {
                $str .= "<th>$val</th>";
            }
        }
        $str .= "</tr>";
        if (sizeof($arrSupplierCek) > 0) {
            $no = 0;
            foreach ($arrSupplierCek as $trid => $trSpec) {
                $no++;
                $str .= "<tr style='background-color:$bgcolor;'>";
                $str .= "<td>$no</td>";
                foreach ($arrHeader2 as $key => $val) {
                    $val_data = isset($trSpec[$key]) ? $trSpec[$key] : "";
                    if (is_array($val)) {
                        $val_data_f = formatField_he_format($val["format"], $val_data);
                        $align = "right";
                    }
                    else {
                        $val_data_f = $val_data;
                        $align = "";
                    }
                    $str .= "<td style='text-align:$align;'>";
                    $str .= $val_data_f;
                    $str .= "</td>";
                    if (is_numeric($val_data)) {
                        if (!isset($totalBawah2[$key])) {
                            $totalBawah2[$key] = 0;
                        }
                        $totalBawah2[$key] += $val_data;
                    }
                }
                $str .= "</tr>";
            }
            $str .= "<tr>";
            $str .= "<th>-</th>";
            foreach ($arrHeader2 as $key => $val) {
                $val_data = isset($totalBawah2[$key]) ? $totalBawah2[$key] : "";
                if (is_array($val)) {
                    $val_data_f = formatField_he_format($val["format"], $val_data);
                    $align = "right";
                }
                else {
                    $val_data_f = $val_data;
                    $align = "";
                }
                $str .= "<th style='text-align:$align;'>";
                $str .= $val_data_f;
                $str .= "</th>";
            }
            $str .= "</tr>";
        }
        $str .= "</table style='border:1px solid black;width:100%;' rules='all'>";
        echo $str;


        mati_disini("---SETOP--- " . __LINE__);
        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");
        cekHijau("<h3>DONE...</h3>");
    }

    public function cekUangMukaPymSrc()
    {
        $tbl_1 = "_rek_pembantu_uang_muka_reference_cache";
        $tbl_2 = "transaksi_uang_muka_source";
        $rekening = "1010050010";
        $periode = "forever";
        $cabang_id = "-1";
        $supplier_id = $_GET["sid"];

        //region cache-------
        $where = array(
            "rekening" => $rekening,
            "cabang_id" => $cabang_id,
            "periode" => $periode,
        );
        if ($supplier_id > 0) {
            $where["extern_id"] = $supplier_id;
        }
        $this->db->where($where);
        $cacheTmp = $this->db->get($tbl_1)->result();
        showLast_query("biru");
        cekBiru(count($cacheTmp));
        //endregion cache-------

        //region pymsrc
        $where = array(
            "extern_label2" => "vendor",
            "cabang_id" => $cabang_id,
            "label" => "uang muka",
        );
        if ($supplier_id > 0) {
            $where["extern_id"] = $supplier_id;
        }
        $this->db->where($where);
        $pymSsourceTmp = $this->db->get($tbl_2)->result();
        showLast_query("kuning");
        cekKuning(count($pymSsourceTmp));
        if (sizeof($pymSsourceTmp) > 0) {
            foreach ($pymSsourceTmp as $pymSsourceSpec) {
                if (!isset($pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["sisa"])) {
                    $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["sisa"] = 0;
                }
                $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["sisa"] += $pymSsourceSpec->sisa;
                if (!isset($pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["terbayar"])) {
                    $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["terbayar"] = 0;
                }
                $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["terbayar"] += $pymSsourceSpec->sisa;

                $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["extern_id"] = $pymSsourceSpec->extern_id;
                $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["extern_nama"] = $pymSsourceSpec->extern_nama;
                $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["extern2_id"] = $pymSsourceSpec->extern2_id;
                $pymUMRelasi[$pymSsourceSpec->extern_id][$pymSsourceSpec->extern2_id]["extern2_nama"] = $pymSsourceSpec->extern2_nama;
            }
        }
        //endregion pymsrc

        $this->db->trans_start();


        // region uang muka ke supplier berelasi po, tampilkan di ui tabel
        $str = "<table rules='all' width='75%' style='border:1px solid black;'>";
        $str .= "<tr>";
        $str .= "<th>no.</th>";
        $str .= "<th>supplier ID</th>";
        $str .= "<th>supplier Nama</th>";
        $str .= "<th>referensi ID</th>";
        $str .= "<th>referensi nomer</th>";
        $str .= "<th>cache</th>";
        $str .= "<th>pym src</th>";
        $str .= "</tr>";
        if (sizeof($cacheTmp) > 0) {
            $no = 0;
            $totalBawah = array();
            foreach ($cacheTmp as $cacheSpec) {
                $extern_id = $cacheSpec->extern_id;
                $extern_nama = $cacheSpec->extern_nama;
                $extern2_id = $cacheSpec->extern2_id;
                $extern2_nama = $cacheSpec->extern2_nama;
                $debet = $cacheSpec->debet;
                $sisa = isset($pymUMRelasi[$extern_id][$extern2_id]["sisa"]) ? $pymUMRelasi[$extern_id][$extern2_id]["sisa"] : 0;
                $bgcolor = "";
                if (($sisa - $debet) > 100) {
                    $bgcolor = "yellow";

                    // region patch pym src
                    $where_update = array(
                        "extern_id" => $extern_id,
                        "extern2_id" => $extern2_id,
                        "label" => "uang muka",
                        "cabang_id" => $cabang_id,
                        "extern_label2" => "vendor",
                    );
                    $data_update = array(
                        "sisa" => $debet,
                    );
                    $this->db->where($where_update);
                    $this->db->update($tbl_2, $data_update);
                    showLast_query("orange");
                    // endregion patch pym src

                }
                if (!isset($totalBawah["debet"])) {
                    $totalBawah["debet"] = 0;
                }
                $totalBawah["debet"] += $debet;
                if (!isset($totalBawah["sisa"])) {
                    $totalBawah["sisa"] = 0;
                }
                $totalBawah["sisa"] += $sisa;

                $no++;
                $str .= "<tr style='background-color:$bgcolor;'>";
                $str .= "<td>$no</td>";
                $str .= "<td>$extern_id</td>";
                $str .= "<td>$extern_nama</td>";
                $str .= "<td>$extern2_id</td>";
                $str .= "<td>$extern2_nama</td>";
                $str .= "<td>" . number_format($debet) . "</td>";
                $str .= "<td>" . number_format($sisa) . "</td>";
                $str .= "</tr>";
            }
            $str .= "<tr>";
            $str .= "<th>-</th>";
            $str .= "<th>-</th>";
            $str .= "<th>-</th>";
            $str .= "<th>-</th>";
            $str .= "<th>-</th>";
            $str .= "<th>" . number_format($totalBawah["debet"]) . "</th>";
            $str .= "<th>" . number_format($totalBawah["sisa"]) . "</th>";
            $str .= "</tr>";
        }
        $str .= "</table>";
        echo $str;
        // endregion uang muka ke supplier berelasi po, tampilkan di ui tabel


        mati_disini("...cek MANUAL cli transaksi... ");
        $this->db->trans_complete() or mati_disini("Gagal saat berusaha  commit transaction!");


    }

    public function cekCashbackInvoice()
    {
        $this->load->model("MdlTransaksi");
        $this->load->model("Mdls/MdlLockerTransaksi");
        // daftar invoice yang dibatalkan
        $tr = New MdlTransaksi();
        $tr->addFilter("jenis='4822'");
        $tr->addFilter("trash_4=1");
        $trTmp = $tr->lookupAll()->result();
        showLast_query("biru");
        cekBiru(count($trTmp));
        $arrTrIDs = array();
        if (sizeof($trTmp) > 0) {
            foreach ($trTmp as $trSpec) {

                $arrTrIDs[$trSpec->id] = $trSpec->id;
            }
//            arrPrint($arrTrIDs);
            // daftar invoice yang sudah diberi cashback
            $tri = New MdlLockerTransaksi();
            $tri->setFilters(array());
            $tri->addFilter("produk_id in ('" . implode("','", $arrTrIDs) . "')");
            $tri->addFilter("jenis='komisi'");
            $tri->addFilter("state='hold'");
            $triTmp = $tri->lookupAll()->result();
            showLast_query("kuning");
            cekKuning(count($triTmp));


        }

    }

    public function patchTransaksiSupplies()
    {

        $this->db->trans_start();


        $this->load->model("MdlTransaksi");
        $tr = New MdlTransaksi();
        $tr->addFilter("jenis='461r'");
        $trTmp = $tr->lookupAll()->result();
        cekBiru(count($trTmp));
        if (sizeof($trTmp) > 0) {
            foreach ($trTmp as $trSpec) {
                $ini_trid = $trSpec->id;
                $trreg = New MdlTransaksi();
                $trreg->setFilters(array());
                $trreg->setJointSelectFields("transaksi_id, main");
                $trreg->addFilter("transaksi_id='$ini_trid'");
                $tmpReg = $trreg->lookupDataRegistries()->result();
                $main = blobDecode($tmpReg[0]->main);

                $transaksi_nilai = $trSpec->transaksi_nilai;
                $transaksi_nilai_main = isset($main["nett"]) ? $main["nett"] : 0;
                $selisih = $transaksi_nilai - $transaksi_nilai_main;
                $selisih = ($selisih < 0) ? ($selisih * -1) : $selisih;
                if ($selisih > 10) {
                    // update
                    $tru = New MdlTransaksi();
                    $where = array(
                        "id" => $ini_trid,
                    );
                    $data = array(
                        "transaksi_nilai" => $transaksi_nilai_main,
                    );
                    $tru->setFilters(array());
                    $tru->updateData($where, $data);
                    showLast_query("orange");
                }
            }
        }

//        mati_disini("---SETOP--- " . __LINE__);
        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");
        cekHijau("<h3>DONE...</h3>");

    }


    //-----
    public function cekRekeningPembantu()
    {

        $rekening = "1010020010";
        $tabel_main = "__rek_master__" . $rekening;
        $tabel_pembantu = "__rek_pembantu_customer__" . $rekening;
        $arrMain = array();
        $arrPembantu = array();
        $bln_thn = "01-2024";
//        $bln_thn = "02-2024";
//        $bln_thn = "03-2024";
//        $bln_thn = "04-2024";
//        $bln_thn = "05-2024";
//        $bln_thn = "06-2024";
//        $bln_thn = "07-2024";
//        $bln_thn = "08-2024";
//        $bln_thn = "09-2024";
//        $bln_thn = "10-2024";
//        $bln_thn = "11-2024";
//        $bln_thn = "12-2024";
//        $bln_thn = "01-2025";
//        $bln_thn = "02-2025";
//        $bln_thn = "03-2025";
//        $bln_thn = "04-2025";
//        $bln_thn = "05-2025";
//        $bln_thn = "06-2025";
//        $bln_thn = "07-2025";
//        $bln_thn = "08-2025";
//        $bln_thn = "09-2025";
//        $bln_thn = "10-2025";
//        $bln_thn = "11-2025";
//        $bln_thn = "12-2025";

        $bln_thn_ex = explode("-", $bln_thn);
        $bln = $bln_thn_ex[0];
        $thn = $bln_thn_ex[1];
        $where = array(
            "month(dtime)" => "$bln",
            "year(dtime)" => "$thn",
        );
        $this->db->where($where);
        $query_main = $this->db->get($tabel_main)->result();
        showLast_query("kuning");
        cekKuning(count($query_main));
        if (sizeof($query_main) > 0) {
            foreach ($query_main as $spec_main) {
                $debet = $spec_main->debet;
                $kredit = $spec_main->kredit;
                unset($spec_main->debet);
                unset($spec_main->kredit);
                if (!isset($arrMain[$spec_main->transaksi_id])) {
                    $arrMain[$spec_main->transaksi_id] = (array)$spec_main;
                }
                if (!isset($arrMain[$spec_main->transaksi_id]["debet"])) {
                    $arrMain[$spec_main->transaksi_id]["debet"] = 0;
                }
                if (!isset($arrMain[$spec_main->transaksi_id]["kredit"])) {
                    $arrMain[$spec_main->transaksi_id]["kredit"] = 0;
                }
                $arrMain[$spec_main->transaksi_id]["debet"] += $debet;
                $arrMain[$spec_main->transaksi_id]["kredit"] += $kredit;
            }
        }


        $where = array(
            "month(dtime)" => "$bln",
            "year(dtime)" => "$thn",
        );
        $this->db->where($where);
        $query_pembantu = $this->db->get($tabel_pembantu)->result();
        showLast_query("ungu");
        cekUngu(count($query_pembantu));
        if (sizeof($query_pembantu) > 0) {
            foreach ($query_pembantu as $spec_pembantu) {
                $debet = $spec_pembantu->debet;
                $kredit = $spec_pembantu->kredit;
                unset($spec_pembantu->debet);
                unset($spec_pembantu->kredit);
                if (!isset($arrPembantu[$spec_pembantu->transaksi_id])) {
                    $arrPembantu[$spec_pembantu->transaksi_id] = (array)$spec_pembantu;
                }
                if (!isset($arrPembantu[$spec_pembantu->transaksi_id]["debet"])) {
                    $arrPembantu[$spec_pembantu->transaksi_id]["debet"] = 0;
                }
                if (!isset($arrPembantu[$spec_pembantu->transaksi_id]["kredit"])) {
                    $arrPembantu[$spec_pembantu->transaksi_id]["kredit"] = 0;
                }
                $arrPembantu[$spec_pembantu->transaksi_id]["debet"] += $debet;
                $arrPembantu[$spec_pembantu->transaksi_id]["kredit"] += $kredit;
            }
        }

        //------
        $arrKeysmain = array_keys($arrMain);
        $arrKeyspembantu = array_keys($arrPembantu);
        $arrDiff_1 = array_diff($arrKeysmain, $arrKeyspembantu);
        $arrDiff_2 = array_diff($arrKeyspembantu, $arrKeysmain);
        arrPrintCyan($arrDiff_1);
        arrPrintKuning($arrDiff_2);

        $header = array(
            "transaksi_id" => "trid",
            "transaksi_no" => "nomer",
            "debet" => "debet",
            "kredit" => "kredit",
            "pembantu_debet" => "pembantu_debet",
            "pembantu_kredit" => "pembantu_kredit",
        );
        $header_summary = array(
            "debet" => "debet",
            "kredit" => "kredit",
            "pembantu_debet" => "pembantu_debet",
            "pembantu_kredit" => "pembantu_kredit",
        );

        $str = "<table rules='all' style='border:1px solid black;' width='100%'>";
        $str .= "<tr>";
        $str .= "<th>No.</th>";
        foreach ($header as $key => $val) {
            $str .= "<th>$val</th>";
        }
        $str .= "</tr>";

        $no = 0;
        if (sizeof($arrMain) > 0) {
            foreach ($arrMain as $trid => $spec_main) {
                if (isset($arrPembantu[$trid])) {
                    foreach ($arrPembantu[$trid] as $mkey => $mval) {
                        $new_key = "pembantu_" . $mkey;
                        $spec_main[$new_key] = $mval;
                    }
                }

                $selisih_debet = ($spec_main["debet"] - $spec_main["pembantu_debet"]);
                $selisih_debet = ($selisih_debet < 0) ? ($selisih_debet * -1) : $selisih_debet;
                $selisih_kredit = ($spec_main["kredit"] - $spec_main["pembantu_kredit"]);
                $selisih_kredit = ($selisih_kredit < 0) ? ($selisih_kredit * -1) : $selisih_kredit;
                if ($selisih_debet > 1) {
                    $bgcolor = "yellow";
                }
                elseif ($selisih_kredit > 1) {
                    $bgcolor = "pink";
                }
                else {
                    $bgcolor = "";
                }

                $no++;
                $str .= "<tr style='background-color:$bgcolor;'>";
                $str .= "<td>$no</td>";
                foreach ($header as $key => $val) {
                    $new_val = isset($spec_main[$key]) ? $spec_main[$key] : "-";
                    $str .= "<td>$new_val</td>";
                    if (array_key_exists($key, $header_summary)) {
                        if (!isset($totalBawah[$key])) {
                            $totalBawah[$key] = 0;
                        }
                        $totalBawah[$key] += $new_val;
                    }
                }
                $str .= "</tr>";
            }

            $selisih_debet = ($totalBawah["debet"] - $totalBawah["pembantu_debet"]);
            $selisih_debet = ($selisih_debet < 0) ? ($selisih_debet * -1) : $selisih_debet;
            $selisih_kredit = ($totalBawah["kredit"] - $totalBawah["pembantu_kredit"]);
            $selisih_kredit = ($selisih_kredit < 0) ? ($selisih_kredit * -1) : $selisih_kredit;
            if ($selisih_debet > 1) {
                $bgcolor = "yellow";
            }
            elseif ($selisih_kredit > 1) {
                $bgcolor = "pink";
            }
            else {
                $bgcolor = "";
            }
            $str .= "<tr style='background-color:$bgcolor;'>";
            $str .= "<th>-</th>";
            foreach ($header as $key => $val) {
                $new_val = isset($totalBawah[$key]) ? $totalBawah[$key] : "-";
                $str .= "<th>$new_val</th>";

            }
            $str .= "</tr>";
        }

        $str .= "</table>";
        echo $str;
    }

    // part produk
    public function cekPartProduk()
    {
        $this->load->model("Mdls/MdlProduk");
        $this->load->model("Mdls/MdlModelIndoor_1");
        $indoors = array();

        $mi = New MdlModelIndoor_1();
        $miTmp = $mi->lookupAll()->result();
        if (sizeof($miTmp) > 0) {
            foreach ($miTmp as $miSpec) {
                $indoors[$miSpec->id] = array(
                    "id" => $miSpec->id,
                    "nama" => $miSpec->nama,
                    "sku" => $miSpec->sku,
                );
            }
        }
//        arrPrintCyan($indoors);

        $p = New MdlProduk();
        $p->addFilter("jml_serial>1");
        $pTmp = $p->lookupAll()->result();
        showLast_query("biru");
        cekBiru("data ada: " . count($pTmp));
        if (sizeof($pTmp) > 0) {
            foreach ($pTmp as $pSpec) {
                $produk_id = $pSpec->id;
                $indoor_id_1 = $pSpec->indoor_id_1;
                $indoor_id_2 = $pSpec->indoor_id_2;
                $indoor_sku_1 = $pSpec->indoor_sku_1;
                $indoor_sku_2 = $pSpec->indoor_sku_2;
                if (trim($indoor_sku_1) != trim($indoors[$indoor_id_1]["sku"])) {
                    cekHere("[pid: $produk_id] [indoor_id_1: $indoor_id_1] [$indoor_sku_1] || --> " . $indoors[$indoor_id_1]["sku"]);
                }
                if (trim($indoor_sku_2) != trim($indoors[$indoor_id_2]["sku"])) {
                    cekKuning("[pid: $produk_id] [indoor_id_2: $indoor_id_2] [$indoor_sku_2] || --> " . $indoors[$indoor_id_2]["sku"]);
                }

            }
        }

        cekHijau("<h3>CEK SELESAI...</h3>");
    }

    // cek saldi um relasi (penjualan tunai)
    public function cekUangMuka($cab_id = NULL, $gud_id = NULL)
    {
        $this->load->model("MdlTransaksi");
        $cabang_id = ($cab_id != NULL) ? $cab_id : "1";
        $gudang_id = ($gud_id != NULL) ? $gud_id : "-10";
        $sesionReplacer = array(
            "cabang_id" => $cabang_id,
            "gudang_id" => $gudang_id,
        );
        $arrSisaUangMuka = array();
        $jenisTr = "999";
        $rekening = "2010050";
        $subrekening = "2010050050";
        $com = "ComRekeningPembantuCustomerDetail";

        // region saldo um relasi
        $this->load->model("Coms/$com");
        $crd = New $com();
        $crd->addFilter("cabang_id='$cabang_id'");
        $crd->addFilter("extern2_id='$subrekening'");
        $crd->addFilter("kredit>100");
        $crdTmp = $crd->fetchBalances($rekening);
        showLast_query("biru");
        cekBiru(count($crdTmp));
        // endregion saldo um relasi
        //mati_disini(__LINE__);

        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            // region so aktif
            $tr = new MdlTransaksi();
            $tr->addFilter("jenis_top in ('5822spo','5823spo')");
            $tr->addFilter("next_substep_code<>''");
            $tr->addFilter("sub_step_number>0");
            $tr->addFilter("valid_qty>0");
            $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
            showLast_query("kuning");
            cekKuning(count($tmpHist));
            if (sizeof($tmpHist) > 0) {
                foreach ($tmpHist as $row) {
                    if ($row->ids_his != "") {
                        $hist = blobDecode($row->ids_his);
                        foreach ($hist as $step_his => $hisSpec) {
                            $arrTransID[] = $row->transaksi_id;
                            $arrTransHist[] = $hisSpec['trID'];
                            $arrIdsHist[$row->transaksi_id] = array(
                                "referenceID__" . $step_his => $hisSpec['trID'],
                                "referenceNumber__" . $step_his => $hisSpec['nomer'],
                                "referenceNomer__" . $step_his => $hisSpec['nomer'],
                                "referenceDtime__" . $step_his => $hisSpec['dtime'],
                                "referenceFulldate__" . $step_his => $hisSpec['fulldate'],
                            );
                        }
                    }
                }
                $pairRegistries = array("main", "items");
                $selectKolom = implode(",", $pairRegistries) . ", transaksi_id";
                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());
                $trReg->setJointSelectFields($selectKolom);
                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        //                    arrPrintWebs($regRow);
                        foreach ($regRow as $key_reg => $val_reg) {
                            if ($val_reg == null) {
                                $val_reg = blobEncode(array());
                            }
                            if ($key_reg != "transaksi_id") {
                                $tmpReg_result[$regRow->transaksi_id][$key_reg] = blobDecode($val_reg);
                            }
                        }

                    }
                }
                foreach ($tmpHist as $row) {
                    $transaksi_idd = $row->transaksi_id;
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->transaksi_id]))) {
                        foreach ($tmpReg_result[$row->transaksi_id] as $param => $eReg) {
                            switch ($param) {
                                case "main":
                                    foreach ($eReg as $k => $v) {
                                        if (($k != null) && !isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                    break;
                                case "items":
                                    if (sizeof($extHistoryFields2) > 0) {
                                        foreach ($extHistoryFields2 as $k1 => $v1) {
                                            if (is_array($v1)) {
                                                $kolom = $v1['kolom'];
                                                $format = $v1['format'];
                                                if (($k1 != null) && !isset($row->$k1)) {
                                                    $tmpDetail = "";
                                                    foreach ($eReg as $eeReg) {
                                                        $valDetail = formatField_he_format($format, $eeReg[$kolom]);
                                                        $tmpDetail .= "<span>$valDetail</span><br>";
                                                    }
                                                    $row->$k1 = $tmpDetail;
                                                }
                                            }
                                            else {
                                                if (($k1 != null) && !isset($row->$k1)) {
                                                    $tmpDetail = "";
                                                    foreach ($eReg as $eeReg) {
                                                        $valDetail = formatField_he_format("nomer", $eeReg[$v1]);
                                                        $tmpDetail .= "<span>$valDetail</span><br>";
                                                    }
                                                    $row->$k1 = $tmpDetail;
                                                }
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                    }
//                arrPrintPink($row);
                    if ($row->step_number > 1) {
                        $grand_pembulatan = $row->grand_pembulatan;
                        $TransData[$row->customers_id]["customer_id"] = $row->customers_id;
                        $TransData[$row->customers_id]["customer_nama"] = $row->customers_nama;
                        $TransData[$row->customers_id]["relasi_so"][$row->transaksi_id] = $row->nomer;
                        if (!isset($TransData[$row->customers_id]["relasi_so_nilai"])) {
                            $TransData[$row->customers_id]["relasi_so_nilai"] = 0;
                        }
                        $TransData[$row->customers_id]["relasi_so_nilai"] += $grand_pembulatan;
                    }
//                break;
                }
            }
            // endregion so aktif
        }

        $header = array(
            "extern_id" => "id konsumen",
            "extern_nama" => "nama konsumen",
            "kredit" => "saldo",
            "relasi_so" => "relasi",
            "relasi_so_nilai" => "nilai relasi<br>aktif",
            "selisih" => "nilai relasi<br>tidak aktif",
        );

        $str = "<table rules='all' width='100%' style='border:1px solid black;'>";
        $str .= "<tr>";
        $str .= "<th>no.</th>";
        foreach ($header as $key => $val) {
            $str .= "<th>$val</th>";
        }
        $str .= "</tr>";


        $no = 0;
        foreach ($crdTmp as $spec) {
            if (isset($TransData[$spec->extern_id])) {
                foreach ($TransData[$spec->extern_id] as $ikey => $ival) {
                    $spec->$ikey = $ival;
                }
            }
            $customer_id = $spec->extern_id;
            $customer_nama = $spec->extern_nama;
            $saldo = $spec->kredit;
            $saldo_relasi = $spec->relasi_so_nilai;
            $selisih = $saldo - $saldo_relasi;
            $spec->selisih = $selisih;
            if ($selisih > 100) {

                $no++;
                $str .= "<tr>";
                $str .= "<td>$no</td>";
                foreach ($header as $key => $val) {
                    $align = "left";
                    $isi = isset($spec->$key) ? $spec->$key : "-";
                    if (is_numeric($isi)) {
                        if (!isset($totalBawah[$key])) {
                            $totalBawah[$key] = 0;
                        }
                        $totalBawah[$key] += $isi;
                        $align = "right";
                    }

                    switch ($key) {
                        case "relasi_so":
                            $isi = implode("<br>", $isi);
                            break;
                        case "selisih":
                        case "kredit":
                        case "relasi_so_nilai":
                            $isi = number_format($isi, "0", ".", ",");
                            break;
                    }
                    $str .= "<td style='text-align:$align;'>$isi</td>";

                }
                $str .= "</tr>";

                $arrSisaUangMuka[$customer_id] = array(
//                    "cabang_id" => $cabang_id,
//                    "gudang_id" => 0,
//                    "customer_id" => $customer_id,
//                    "customer_nama" => $customer_nama,
//                    "nilai" => $selisih,
                    "id" => $customer_id,
                    "nama" => $customer_nama,
                    "name" => $customer_nama,
                    "hpp" => $selisih,
                    "harga" => $selisih,
                    "jml" => 1,
                    "qty" => 1,
                    "reference_nomer" => "",
                    "keterangan_detail" => "",
                );
            }
        }
        $str .= "<tr>";
        $str .= "<th>-</th>";
        foreach ($header as $key => $val) {
            $isi = isset($totalBawah[$key]) ? $totalBawah[$key] : "-";
            $str .= "<th>$isi</th>";
        }
        $str .= "</tr>";

        $str .= "</table>";
        echo $str;

        cekHitam(count($arrSisaUangMuka));
        return $arrSisaUangMuka;
    }

    //-------------------------------------------
    public function koreksiAdjustment()
    {
        $this->load->model("MdlTransaksi");
        $this->load->model("Mdls/MdlProduk2");


        // load transaksi opname, mengambil data yang akan dieksekusi
        $cabangID = "34";
        $cabangNama = "CABANG GADING SERPONG";
        $gudangID = "-340";
        $gudangNama = "default warehouse at branch #34";

        $olehID = "100";
        $olehNama = "system";
        $supplierID = "0";
        $supplierNama = "";
        $pihakID = "0";
        $pihakNama = "";
        $jenis = "999";
        $this->jenisTr = $jenisTr = "999";
        $jenisTrMaster = "999";
        $dtime = date("Y-m-d H:i:s");
        $fulldate = date("Y-m-d");
        $ppnFactor = 11;
        $divID = 18;
        $cash_account = 0;
        $cash_account_nama = "";
        $referenceID = 0;
        $referenceNomer = "";
        $referenceJenis = "";
        $modul_transaksi = "adjustment";
        $tCodeTargetJenisTransaksi = $target_transaksi = $jenisTrMaster;
        $keterangan = "koreksi/pindah uang muka atas nama konsumen (umum) dari cabang ke dc/pusat";

        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            $arrDataDetail = array(
                "3143" => array(
                    "id" => "3143",
                    "nama" => "GREE CASEETTE 5PK GU140T/A-K",
                    "name" => "GREE CASEETTE 5PK GU140T/A-K",
                    "hpp" => "21687387",
                    "harga" => "21687387",
                    "jml" => "8",
                    "qty" => "8",
                    "reference_nomer" => "",
                    "keterangan_detail" => "",
                ),
                "24407" => array(
                    "id" => "24407",
                    "nama" => "GREE GWC-18N1/A",
                    "name" => "GREE GWC-18N1/A",
                    "hpp" => "5562208",
                    "harga" => "5562208",
                    "jml" => "4",
                    "qty" => "4",
                    "reference_nomer" => "",
                    "keterangan_detail" => "",
                ),
            );
        }
        else {
            $arrDataDetail = $this->cekUangMuka($cabangID, $gudangID);
        }

//        arrPrintPink($arrDataDetail);
//        mati_disini(__LINE__);

        $this->db->trans_start();


        $mainGate = array(
            "olehID" => $olehID,
            "olehName" => $olehNama,
            "sellerID" => "",
            "sellerName" => "",
            "pihakID" => $pihakID,
            "pihakName" => $pihakNama,
            "supplierID" => $supplierID,
            "supplierNama" => $supplierNama,
            "supplier2ID" => $supplier2ID,
            "supplier2Nama" => $supplier2Nama,
            "placeID" => $cabangID,
            "placeName" => $cabangNama,
            "cabangID" => $cabangID,
            "cabangName" => $cabangNama,
            "gudangID" => $gudangID,
            "gudangName" => $gudangNama,
            "place2ID" => $cabang2ID,
            "place2Name" => $cabang2Nama,
            "cabang2ID" => $cabang2ID,
            "cabang2Name" => $cabang2Nama,
            "gudang2ID" => $gudang2ID,
            "gudang2Name" => $gudang2Nama,
            "tokoEmail" => "",
            "tokoID" => $tokoID,
            "tokoNama" => $tokoNama,
            "jenisTr" => $jenis,
            "jenisTrMaster" => $jenisTrMaster,
            "jenisTrTop" => $jenis,
            "jenisTrName" => "",
            "stepNumber" => "",
            "stepCode" => $jenis,
            "dtime" => $dtime,
            "fulldate" => $fulldate,
            "ppnFactor" => $ppnFactor,
            "dummyElement" => "yes",
            "dummyElement__label" => "yes",
            "dummyElement__name" => "yes",
            "divID" => $divID,
            "jenis" => $jenis,
            "transaksi_jenis" => $jenis,
            "next_step_code" => $jenis,
            "next_group_code" => "o_holding",
            "step_number" => 1,
            "step_current" => 1,
            "longitude" => "",
            "lattitude" => "",
            "accuracy" => "",
            "description" => $keterangan,
            "keterangan" => $keterangan,

            "cash_account" => $cash_account,
            "cash_account_nama" => $cash_account_nama,
            "referenceID" => $referenceID,
            "referenceNomer" => $referenceNomer,
            "referenceJenis" => $referenceJenis,
            "reference_id" => $referenceID,
            "reference_nomer" => $referenceNomer,
            "reference_jenis" => $referenceJenis,

        );
        $tableIn = array(
            "master" => array(
                "jenis_master" => "jenisTrMaster",
                "jenis_top" => "jenisTrTop",
                "jenis" => "jenisTr",
                "jenis_label" => "jenisTrName",
                "div_id" => "divID",
                "div_nama" => "divName",
                "dtime" => "dtime",
                "fulldate" => "fulldate",
                "oleh_id" => "olehID",
                "oleh_nama" => "olehName",
                "customers_id" => "pihakID",
                "customers_nama" => "pihakName",
                "cabang_id" => "placeID",
                "cabang_nama" => "placeName",
                "transaksi_nilai" => "new_net2",
                "transaksi_jenis" => "jenisTr",
                "keterangan" => "description",
                "gudang_id" => "gudangID",
                "gudang_nama" => "gudangName",
                "toko_id" => "tokoID",
                "toko_nama" => "tokoName",
                "reference_id" => "referenceID",
                "reference_nomer" => "referenceNomer",
                "reference_jenis" => "referenceJenis",

            ),
            "detail" => array(
                "dtime" => "dtime",
                "produk_id" => "id",
                "produk_kode" => "produk_kode",
                "produk_label" => "label",
                "produk_nama" => "name",
                "produk_ord_jml" => "qty",
                "produk_ord_hrg" => "harga",
                "satuan" => "satuan",
            ),
        );

        $harga_pokok = 0;
        $persediaan_produk = 0;
        $hutang_ke_pusat = 0;
        $piutang_cabang = 0;
        $laba_lain_lain = 0;
        $hutang_dagang = 0;

        $detailGate = array();
        $pakai_ini = 1;
        if ($pakai_ini == 1) {
            $total_nilai = 0;
            $arrProdukDatas = array();
            $arrprodukIDs = $arrDataDetail;
            $arrprodukIDKey = array_keys($arrprodukIDs);
            $pakai_ini = 0;
            if ($pakai_ini == 1) {
                $pr = New MdlProduk2();
                $pr->addFilter("id in ('" . implode("','", $arrprodukIDKey) . "')");
                $prTmp = $pr->lookupAll()->result();
                showLast_query("biru");
                foreach ($prTmp as $prSpec) {
                    $arrProdukDatas[$prSpec->id] = $prSpec;
                }
                foreach ($arrProdukDatas as $pid => $specc) {
                    $pnama = $specc->nama;
                    $kode = $specc->kode;
                    $barcode = $specc->barcode;
                    $satuan = $specc->satuan;
                    $jml = $arrprodukIDs[$pid]["jml"];
                    $qty = $arrprodukIDs[$pid]["qty"];
                    $hpp = $arrprodukIDs[$pid]["hpp"];
                    $harga = $arrprodukIDs[$pid]["harga"];
                    $target_id = $arrprodukIDs[$pid]["target_id"];
//                $sub_hpp = $hpp * $jml;
//                $sub_harga = $harga * $jml;
                    $sub_hpp = $hpp * $jml;
                    $sub_harga = $harga * $jml;
                    $total_nilai += $sub_hpp;
                    $detailGate[$pid] = array(
                        "handler" => "opname/_processSelectProduct",
                        "target_id" => $target_id,
                        "id" => $pid,
                        "jml" => $jml,
                        "harga" => $harga,
                        "subtotal" => 0,
                        "satuan" => "gram",
                        "discount_persen" => 0,
                        "discount_qty" => 0,
                        "hpp" => $hpp,
                        "nama" => $pnama,
                        "kode" => $kode,
                        "barcode" => $barcode,
                        "no_part" => 0,
                        "label" => "",
                        "ppn" => 0,
                        "stok" => 0,
                        "debet" => 0,
                        "kredit" => 0,
                        "qty_selisih" => 0,
                        "qty" => $qty,
                        "name" => $pnama,
                        "sub_harga" => $sub_harga,
                        "sub_subtotal" => $sub_harga,
                        "sub_discount_persen" => 0,
                        "sub_discount_qty" => 0,
                        "sub_hpp" => $sub_hpp,
                        "sub_no_part" => 0,
                        "sub_ppn" => 0,
                        "sub_stok" => 0,
                        "sub_debet" => 0,
                        "sub_kredit" => 0,
                        "sub_qty_selisih" => 0,

                        "next_substep_code" => $jenis,
                        "next_subgroup_code" => "o_holding",
                        "sub_step_number" => 1,
                        "sub_step_current" => 1,
                    );
                }
            }
            else {
                // kalau transaksi detailnya
                foreach ($arrprodukIDs as $pid => $specc) {
                    $sub_hpp = $specc["jml"] * $specc["hpp"];
                    $sub_harga = $specc["jml"] * $specc["harga"];
                    $total_nilai += $sub_harga;
                    $detailGate[$pid] = array(
                        "handler" => "",
                        "id" => $pid,
                        "jml" => $specc["jml"],
                        "harga" => $specc["harga"],
                        "subtotal" => 0,
                        "satuan" => "",
                        "discount_persen" => 0,
                        "discount_qty" => 0,
                        "hpp" => $specc["hpp"],
                        "nama" => $specc["nama"],
                        "kode" => "",
                        "barcode" => "",
                        "no_part" => 0,
                        "label" => "",
                        "ppn" => 0,
                        "stok" => 0,
                        "debet" => 0,
                        "kredit" => 0,
                        "qty_selisih" => 0,
                        "qty" => $specc["qty"],
                        "name" => $specc["name"],
                        "sub_harga" => $sub_harga,
                        "sub_subtotal" => $sub_harga,
                        "sub_discount_persen" => 0,
                        "sub_discount_qty" => 0,
                        "sub_hpp" => $sub_hpp,
                        "sub_no_part" => 0,
                        "sub_ppn" => 0,
                        "sub_stok" => 0,
                        "sub_debet" => 0,
                        "sub_kredit" => 0,
                        "sub_qty_selisih" => 0,

                        "next_substep_code" => $jenis,
                        "next_subgroup_code" => "o_holding",
                        "sub_step_number" => 1,
                        "sub_step_current" => 1,
                    );
                    foreach ($specc as $dkey => $dval) {
                        $detailGate[$pid][$dkey] = $dval;
                    }
                }
            }

            //region 1 produk
            foreach ($detailGate as $pid => $spec) {
                foreach ($mainGate as $key => $val) {
                    $spec[$key] = $val;
                }
                $detailGate[$pid] = $spec;
                foreach ($tableIn["detail"] as $ikey => $ival) {
                    $tableIn_detail[$pid][$ikey] = isset($spec[$ival]) ? $spec[$ival] : "";
                }
            }
            //endregion
        }
        else {

        }


        foreach ($tableIn["master"] as $key => $val) {
            $tableIn_master[$key] = isset($mainGate[$val]) ? $mainGate[$val] : "";
        }

//        $mainGate["hpp"] = -$harga_pokok;
//        $mainGate["persediaan_produk"] = -$persediaan_produk;
//        $mainGate["hutang_ke_pusat"] = $hutang_ke_pusat;
//        $mainGate["piutang_cabang_minus_3"] = $piutang_cabang;
//        $mainGate["piutang_dagang"] = -$piutang_dagang;
//        $mainGate["laba_lain_lain"] = -$laba_lain_lain;
//        $mainGate["hutang_dagang"] = -$hutang_dagang;
//        $mainGate["hutang_dagang_detail_1"] = $hutang_dagang_detail_1;
//        $mainGate["hutang_dagang_detail_2"] = -$hutang_dagang_detail_2;
//        $mainGate["modal"] = -$modal;
//        $mainGate["ppn_masukan"] = $ppn_masukan;
//        $mainGate["ppn_masukan_jasa"] = -$ppn_masukan_jasa;
//        $mainGate["kas"] = $kas;
//        $mainGate["kas_minus"] = -$kas_minus;
//        $mainGate["hutang_ke_konsumen"] = -$hutang_ke_konsumen;
//        $mainGate["hutang_ke_konsumen_noppn"] = $hutang_ke_konsumen_noppn;
//        $mainGate["hutang_ke_konsumen_noppn_minus"] = -$hutang_ke_konsumen_noppn;
//        $mainGate["titipan_tanpa_relasi"] = $titipan_tanpa_relasi;
//        $mainGate["titipan_dengan_relasi"] = -$titipan_dengan_relasi;

        $this->cCode = $cCode = "_TR_" . $jenis;
        $this->cCodeData[$cCode] = array(
            "main" => $mainGate,
            "items" => $detailGate,
            "tableIn_master" => $tableIn_master,
            "tableIn_detail" => $tableIn_detail,
        );
        $componentsDetailLoop = true;
        $comsPrefix = "Com";
        $comsLocation = "Coms";
        $compValidators = ($this->config->item('transaksi_value_required_components') != null) ? $this->config->item('transaksi_value_required_components') : array();
        $runCliComponentDetail = false;
        $jenisTrTarget = $jenis;

        //--------------------------
        $preProcessor = array(
            "master" => array(),
            "detail" => array(),
        );
        $components = array(
            "master" => array(),
            "detail" => array(),
        );
        $postProcessor = array(
            "master" => array(),
            "detail" => array(),
        );
        //--------------------------


        // MEMBUAT TRANSAKSI
        $pakai_ini = 1;
        if ($pakai_ini == 1) {
            //region dynamic counters

            $counters = array(
                "stepCode|placeID",
                "stepCode|olehID",
                "stepCode|placeID|olehID",
            );
            $formatNota = "stepCode|placeID";

            $pakai_ini = 0;
            if ($pakai_ini == 1) {
                //region penomoran receipt
                $this->load->model("CustomCounter");
                $cn = new CustomCounter("transaksi");
                $cn->setType("transaksi");
                $cn->setModul($modul_transaksi);
                $cn->setStepCode($tCodeTargetJenisTransaksi);
                $configCustomParams = $counters;
                if (sizeof($configCustomParams) > 0) {
                    $cContent = array();
                    foreach ($configCustomParams as $i => $cRawParams) {
                        $cParams = explode("|", $cRawParams);
                        $cValues = array();
                        foreach ($cParams as $param) {
                            $cValues[$i][$param] = $this->cCodeData[$cCode]["main"][$param];
                        }
                        $cRawValues = implode("|", $cValues[$i]);
                        $paramSpec = $cn->getNewCount($cParams, $cValues[$i], $tokoID);

                        $cContent[$cRawParams][$cRawValues] = $paramSpec["value"];
                        switch ($paramSpec["id"]) {
                            case 0: //===counter type is new
                                $addData = array(
//                                "toko_id" => $tokoID,
//                                "toko_nama" => $tokoNama,
                                );
                                $paramKeyRaw = print_r($cParams, true);
                                $paramValuesRaw = print_r($cValues[$i], true);
                                $cn->writeNewCount($cParams, $cValues[$i], $paramKeyRaw, $paramValuesRaw, $addData);
                                break;
                            default: //===counter to be updated
                                $cn->updateCount($paramSpec["id"], $paramSpec["value"]);
                                break;
                        }
                    }
                }

                $appliedCounters = base64_encode(serialize($cContent));
                $appliedCounters_inText = print_r($cContent, true);

                $cn = new CustomCounter("transaksi");
                $cn->setType("transaksi");
                $cn->setModul($modul_transaksi);
                $cn->setStepCode($tCodeTargetJenisTransaksi);
                $counterForNumber = array($formatNota);
                foreach ($counterForNumber as $i => $c0RawParams) {
                    $c0Params = explode(",", $c0RawParams);
                    foreach ($c0Params as $k => $cRawParams) {
                        $dParams = explode("|", $cRawParams);
                        if (count($dParams) > 1) {
                            if (!in_array($cRawParams, $counters)) {
                                die(__LINE__ . "( $cRawParams ) Used number should be registered in counters config as well");
                            }
                        }
                    }
                }

                $tmpNomorNota = "";
                $arrNomorNota = array();
                foreach ($counterForNumber as $i => $c0RawParams) {
                    $c0Params = explode(",", $c0RawParams);
                    $c0Values = array();
                    foreach ($c0Params as $k => $cRawParams) {
                        $arrRawParams = explode("|", $cRawParams);
                        if (sizeof($arrRawParams) > 1) {
                            $cRawParamsValues = array();
                            foreach ($arrRawParams as $key) {
                                $cRawParamsValues[$key] = $this->cCodeData[$cCode]['main'][$key];
                            }
                            $cRawParamsValuesK = implode("|", array_keys($cRawParamsValues));
                            $cRawParamsValuesV = implode("|", $cRawParamsValues);
                            $arrNomorNota[] = digit_4($cContent[$cRawParamsValuesK][$cRawParamsValuesV]);
                        }
                        else {
                            $cRawParamsValuesK = $arrRawParams[0];
                            $cRawParamsValuesV = $this->cCodeData[$cCode]['main'][$arrRawParams[0]];
                            if ($arrRawParams[0] == "fulldate") {
                                $arrNomorNota[] = $arrRawParams[0] . "|" . date("mY", strtotime($cRawParamsValuesV));
                            }
                            elseif ($arrRawParams[0] == "stepCode") {
                                $arrNomorNota[] = $cRawParamsValuesV; //ini harus ori tidak boleh di masking/ diformat
//                            $arrNomorNota[] = digit_4($cContent[$cRawParamsValuesK][$cRawParamsValuesV]);
                            }
                            elseif ($arrRawParams[0] == "placeID") {
                                $arrNomorNota[] = digit_2($cRawParamsValuesV);
                            }
                            elseif ($arrRawParams[0] == "customerID") {
                                $arrNomorNota[] = digit_4($cRawParamsValuesV);
                            }
                            elseif ($arrRawParams[0] == "olehID") {
                                $arrNomorNota[] = digit_4($cRawParamsValuesV);
                            }
                            elseif ($arrRawParams[0] == "supplierID") {
                                $arrNomorNota[] = digit_4($cRawParamsValuesV);
                            }
                            else {
                                $arrNomorNota[] = $cRawParamsValuesV;
                            }
                        }
                    }
                }

                $stepNumber = 1;
                $tmpNomorNota = implode("-", $arrNomorNota);
                cekMerah(":: $tmpNomorNota ::");
                mati_disini(__LINE__);
                //endregion penomoran receipt
            }
            else {
                $this->load->model("CustomCounter");
                $cn = new CustomCounter("transaksi");
                $cn->setType("transaksi");
                $cn->setModul($modul_transaksi);
                $cn->setStepCode($tCodeTargetJenisTransaksi);
                $counterForNumber = array($formatNota);
                if (!in_array($counterForNumber[0], $counters)) {
                    mati_disini(__LINE__ . " Used number should be registered in 'counters' config as well");
                }
                echo "<div style='background:#ff7766;'>";
                foreach ($counterForNumber as $i => $cRawParams) {
                    $cParams = explode("|", $cRawParams);
                    $cValues = array();
                    foreach ($cParams as $param) {
                        $cValues[$i][$param] = $this->cCodeData[$cCode]['main'][$param];
                    }
                    $cRawValues = implode("|", $cValues[$i]);
                    $paramSpec = $cn->getNewCount($cParams, $cValues[$i]);
                }
                echo "</div style='background:#ff7766;'>";
                //arrPrintWebs($paramSpec);

                $tmpNomorNota = $paramSpec['paramString'];
                $tmpNomorNotaAlias = formatNota("nomer_nolink", $tmpNomorNota);
                cekMerah("[$tmpNomorNota] [$tmpNomorNotaAlias]");

                $cn = new CustomCounter("transaksi");
                $cn->setType("transaksi");
                $cn->setType("transaksi");
                $cn->setModul($modul_transaksi);
                $cn->setStepCode($tCodeTargetJenisTransaksi);
                $configCustomParams = $counters;
                $configCustomParams[] = "stepCode";
                //arrPrint($configCustomParams);
                if (sizeof($configCustomParams) > 0) {
                    $cContent = array();
                    foreach ($configCustomParams as $i => $cRawParams) {
                        $cParams = explode("|", $cRawParams);
                        $cValues = array();
                        foreach ($cParams as $param) {
                            $cValues[$i][$param] = $this->cCodeData[$cCode]['main'][$param];
                        }
                        $cRawValues = implode("|", $cValues[$i]);
                        $paramSpec = $cn->getNewCount($cParams, $cValues[$i]);

                        $cContent[$cRawParams][$cRawValues] = $paramSpec['value'];
                        switch ($paramSpec['id']) {
                            case 0: //===counter type is new
                                $paramKeyRaw = print_r($cParams, true);
                                $paramValuesRaw = print_r($cValues[$i], true);
                                $cn->writeNewCount($cParams, $cValues[$i], $paramKeyRaw, $paramValuesRaw);
                                break;
                            default: //===counter to be updated
                                $cn->updateCount($paramSpec['id'], $paramSpec['value']);
                                break;
                        }
                        //echo "<hr>";
                    }
                }
                $appliedCounters = base64_encode(serialize($cContent));
                $appliedCounters_inText = print_r($cContent, true);
//                arrPrint($appliedCounters);
//                mati_disini(__LINE__);

            }


            //region addition on master
            $nextProp = array(
                "num" => 0,
                "code" => "",
                "label" => "",
                "groupID" => "",
            );
            $addValues = array(
                "counters" => $appliedCounters,
                'counters_intext' => $appliedCounters_inText,
                'nomer' => $tmpNomorNota,
                'dtime' => date("Y-m-d H:i:s"),
                'fulldate' => date("Y-m-d"),
                "step_avail" => 1,
                "step_number" => 1,
                "step_current" => 1,
                "next_step_num" => $nextProp["num"],
                "next_step_code" => $nextProp["code"],
                "next_step_label" => $nextProp["label"],
                "next_group_code" => $nextProp["groupID"],
                "tail_number" => 1,
                "tail_code" => "",
            );
            foreach ($addValues as $key => $val) {
                $this->cCodeData[$cCode]["tableIn_master"][$key] = $val;
            }
            //endregion

            //region addition on detail
            $addSubValues = array(
                "sub_step_number" => 1,
                "sub_step_current" => 1,
                "sub_step_avail" => 1,
                "next_substep_num" => $nextProp["num"],
                "next_substep_code" => $nextProp["code"],
                "next_substep_label" => $nextProp["label"],
                "next_subgroup_code" => $nextProp["groupID"],
                "sub_tail_number" => 1,
                "sub_tail_code" => "",
            );
            foreach ($this->cCodeData[$cCode]["tableIn_detail"] as $id => $dSpec) {
                foreach ($addSubValues as $key => $val) {
                    $this->cCodeData[$cCode]["tableIn_detail"][$id][$key] = $val;
                }
            }
            //endregion

            //endregion

            //region numbering tambahan
            $this->load->library("CounterNumber");
            $ccn = new CounterNumber();
            $ccn->setCCode($this->cCode);
            $ccn->setJenisTr($this->jenisTr);
            $ccn->setTransaksiGate($this->cCodeData[$cCode]["tableIn_master"]);
            $ccn->setMainGate($this->cCodeData[$cCode]["main"]);
            $ccn->setItemsGate($this->cCodeData[$cCode]["items"]);

            if (isset($this->cCodeData[$cCode]["items2_sum"])) {
                $ccn->setItems2SumGate($this->cCodeData[$cCode]["items2_sum"]);
            }

            $new_counter = $ccn->getCounterNumber();

            cekHitam("jenistr yang disett dari create " . $this->jenisTr);

            if (isset($new_counter["main"]) && sizeof($new_counter["main"]) > 0) {
                foreach ($new_counter["main"] as $ckey => $cval) {
                    $this->cCodeData[$cCode]["tableIn_master"][$ckey] = $cval;
                    $this->cCodeData[$cCode]["main"][$ckey] = $cval;
                }
            }
            if (isset($new_counter["items"]) && sizeof($new_counter["items"]) > 0) {
                foreach ($new_counter["items"] as $ikey => $iSpec) {
                    foreach ($iSpec as $iikey => $iival) {
                        $this->cCodeData[$cCode]["items"][$ikey][$iikey] = $iival;
                    }
                }
            }
            if (isset($new_counter["items2_sum"]) && sizeof($new_counter["items2_sum"]) > 0) {
                foreach ($new_counter["items2_sum"] as $ikey => $iSpec) {
                    foreach ($iSpec as $iikey => $iival) {
                        $this->cCodeData[$cCode]["items2_sum"][$ikey][$iikey] = $iival;
                    }
                }
            }
            //endregion

            //region MENULIS TRANSAKSIONAL
            if (isset($this->cCodeData[$cCode]["tableIn_master"]) && sizeof($this->cCodeData[$cCode]["tableIn_master"]) > 0) {

                $this->cCodeData[$cCode]["tableIn_master"]['status_4'] = 11;
                $this->cCodeData[$cCode]["tableIn_master"]['trash_4'] = 0;
                if ($runCliComponentDetail == false) {
                    $this->cCodeData[$cCode]["tableIn_master"]['cli'] = 1;
                }
                else {
                    $this->cCodeData[$cCode]["tableIn_master"]['cli'] = 0;
                }

                $tr = new MdlTransaksi();
                $tr->addFilter("transaksi.cabang_id='" . $this->cCodeData[$cCode]["tableIn_master"]['cabang_id'] . "'");
                $insertID = $tr->writeMainEntries($this->cCodeData[$cCode]["tableIn_master"]);
                cekHitam($this->db->last_query());
                $epID = $tr->writeMainEntries_entryPoint($insertID, $insertID, $this->cCodeData[$cCode]["tableIn_master"]);
                $insertNum = $this->cCodeData[$cCode]["tableIn_master"]['nomer'];
                $this->cCodeData[$cCode]["main"]['nomer'] = $insertNum;
                if ($insertID < 1) {
                    die("Gagal saat berusaha  write transaction entry pada " . __FILE__ . " baris " . __LINE__);
                }

                //==transaksi_id dan nomor nota diinject kan ke gate utama
                $injectors = array(
                    "transaksi_id" => $insertID,
                    "nomer" => $tmpNomorNota,
                    "nomer2" => isset($tmpNomorNotaAlias) ? $tmpNomorNotaAlias : "",
                );
                $arrInjectorsTarget = array(
                    "items",
                    "items2_sum",
                    "rsltItems",
                );
                foreach ($injectors as $key => $val) {
                    $this->cCodeData[$cCode]["main"][$key] = $val;
                    foreach ($arrInjectorsTarget as $target) {
                        if (isset($this->cCodeData[$cCode][$target])) {
                            foreach ($this->cCodeData[$cCode][$target] as $xid => $iSpec) {
                                $id = isset($iSpec["id"]) && $iSpec["id"] > 0 ? $iSpec["id"] : $xid;
                                if (isset($this->cCodeData[$cCode][$target][$id])) {
                                    $this->cCodeData[$cCode][$target][$id][$key] = $val;
                                }
                            }
                        }
                    }
                }

                //===signature
                $dwsign = $tr->writeSignature($insertID, array(
                    "nomer" => $this->cCodeData[$cCode]["main"]['nomer'],
                    "step_number" => 1,
                    "step_code" => $this->jenisTr,
//                    "step_name" => $this->configUiModul[$this->jenisTr]["steps"][1]["label"],
//                    "group_code" => $this->configUiModul[$this->jenisTr]["steps"][1]['userGroup'],
//                    "oleh_id" => $this->cCodeData[$cCode]["main"]['olehID'],
//                    "oleh_nama" => $this->cCodeData[$cCode]["main"]['olehName'],
                    "step_name" => "",
                    "group_code" => "",
                    "oleh_id" => "",
                    "oleh_nama" => "",
                    "keterangan" => "",
                    "transaksi_id" => $insertID,
                )) or die("Failed to write signature");

                $idHis = array(
                    $stepNumber => array(
                        "olehID" => $this->cCodeData[$cCode]["main"]['olehID'],
                        "olehName" => $this->cCodeData[$cCode]["main"]['olehName'],
                        "step" => $stepNumber,
                        "trID" => $insertID,
                        "nomer" => $tmpNomorNota,
                        "nomer2" => isset($tmpNomorNotaAlias) ? $tmpNomorNotaAlias : "",
                        "counters" => $appliedCounters,
                        // "counters_intext" => $appliedCounters_inText,
                    ),
                );
                $idHis_blob = blobEncode($idHis);
                $idHis_intext = print_r($idHis, true);
                $tr = new MdlTransaksi();
                $dupState = $tr->updateData(array("id" => $insertID), array(
                    "next_step_num" => $nextProp["num"],
                    "next_step_code" => $nextProp["code"],
                    "next_step_label" => $nextProp["label"],
                    "next_group_code" => $nextProp["groupID"],

                    //===references
                    "id_master" => $insertID,
                    "id_top" => $insertID,
                    "ids_prev" => "",
                    "nomer_top" => $this->cCodeData[$cCode]["main"]['nomer'],
                    "nomers_prev" => "",
                    "jenises_prev" => "",
                    "ids_his" => $idHis_blob,

                )) or die("Failed to update tr next-state!");
                cekHijau($this->db->last_query());
                $addValues = array(
                    //===references
                    "id_master" => $insertID,
                    "id_top" => $insertID,
                    "ids_prev" => "",
                    "nomer_top" => $this->cCodeData[$cCode]["main"]['nomer'],
                    "nomers_prev" => "",
                    "jenises_prev" => "",
                    "ids_his" => $idHis_blob,
                );
                foreach ($addValues as $key => $val) {
                    $this->cCodeData[$cCode]["tableIn_master"][$key] = $val;
                }

            }
            if (isset($this->cCodeData[$cCode]['tableIn_master_values']) && sizeof($this->cCodeData[$cCode]['tableIn_master_values']) > 0) {
                $inserMainValues = array();
                if (isset($this->configValuesModul[$this->jenisTr]["tableIn"]['mainValues'])) {
                    $inserMainValues = array();
                    foreach ($this->configValuesModul[$this->jenisTr]["tableIn"]['mainValues'] as $key => $src) {
                        if (isset($this->cCodeData[$cCode]['tableIn_master_values'][$key])) {
                            $dd = $tr->writeMainValues($insertID, array(
                                "key" => $key,
                                "value" => $this->cCodeData[$cCode]['tableIn_master_values'][$key],
                            ));
                            $inserMainValues[] = $dd;
                        }
                    }
                }
                if (sizeof($inserMainValues) > 0) {
                    $arrBlob = blobEncode($inserMainValues);
                    $this->db->query("UPDATE transaksi SET indexing_main_values = '$arrBlob' WHERE id=$insertID");
                }
            }
            if (isset($this->cCodeData[$cCode]['main_add_values']) && sizeof($this->cCodeData[$cCode]['main_add_values']) > 0) {
                $inserMainValues = array();
                foreach ($this->cCodeData[$cCode]['main_add_values'] as $key => $val) {
                    $dd = $tr->writeMainValues($insertID, array("key" => $key, "value" => $val));
                    $inserMainValues[] = $dd;
                }
                if (sizeof($inserMainValues) > 0) {
                    $arrBlob = blobEncode($inserMainValues);
                    $this->db->query("UPDATE transaksi SET indexing_main_values = '$arrBlob' WHERE id=$insertID");
                }
            }
            if (isset($this->cCodeData[$cCode]['main_inputs']) && sizeof($this->cCodeData[$cCode]['main_inputs']) > 0) {
                foreach ($this->cCodeData[$cCode]['main_inputs'] as $key => $val) {
                    $tr->writeMainValues($insertID, array("key" => $key, "value" => $val));
                }
            }
            if (isset($this->cCodeData[$cCode]['main_add_fields']) && sizeof($this->cCodeData[$cCode]['main_add_fields']) > 0) {
                foreach ($this->cCodeData[$cCode]['main_add_fields'] as $key => $val) {
                    $tr->writeMainFields($insertID, array("key" => $key, "value" => $val));
                }
            }
            if (isset($this->cCodeData[$cCode]['main_applets']) && sizeof($this->cCodeData[$cCode]['main_applets']) > 0) {
                foreach ($this->cCodeData[$cCode]['main_applets'] as $amdl => $aSpec) {
                    $tr->writeMainApplets($insertID, array(
                        "mdl_name" => $amdl,
                        "key" => $aSpec['key'],
                        "label" => $aSpec['labelValue'],
                        "description" => $aSpec['description'],
                    ));
                }
            }
            if (isset($this->cCodeData[$cCode]['main_elements']) && sizeof($this->cCodeData[$cCode]['main_elements']) > 0) {
                foreach ($this->cCodeData[$cCode]['main_elements'] as $elName => $aSpec) {
                    $tr->writeMainElements($insertID, array(
                        "mdl_name" => isset($aSpec['mdl_name']) ? $aSpec['mdl_name'] : "",
                        "key" => isset($aSpec['key']) ? $aSpec['key'] : 0,
                        "value" => isset($aSpec["value"]) ? $aSpec["value"] : "",
                        "name" => $aSpec['name'],
                        "label" => $aSpec["label"],
                        "contents" => isset($aSpec['contents']) ? $aSpec['contents'] : "",
                        "contents_intext" => isset($aSpec['contents_intext']) ? $aSpec['contents_intext'] : "",

                    ));
                    //==nebeng bikin inputLabels
                    $currentValue = "";
                    switch ($aSpec['elementType']) {
                        case "dataModel":
                            $currentValue = $aSpec['key'];
                            break;
                        case "dataField":
                            $currentValue = $aSpec["value"];
                            break;
                    }
                    if (array_key_exists($elName, $relOptionConfigs)) {
                        if (isset($relOptionConfigs[$elName][$currentValue])) {
                            if (sizeof($relOptionConfigs[$elName][$currentValue]) > 0) {
                                foreach ($relOptionConfigs[$elName][$currentValue] as $oValueName => $oValSpec) {
                                    $inputLabels[$oValueName] = $oValSpec["label"];
                                    if (isset($oValSpec['auth'])) {
                                        if (isset($oValSpec['auth']["groupID"])) {
                                            $inputAuthConfigs[$oValueName] = $oValSpec['auth']["groupID"];
                                        }
                                    }
                                }
                            }
                        }
                        else {
                            //						cekKuning("option $currentValue pada $eName TIDAK ada pilihannya");
                        }
                    }
                }
            }
            if (isset($this->cCodeData[$cCode]["tableIn_detail"]) && sizeof($this->cCodeData[$cCode]["tableIn_detail"]) > 0) {
                $insertIDs = array();
                $insertDeIDs = array();
                foreach ($this->cCodeData[$cCode]["tableIn_detail"] as $dSpec) {
                    $insertDetailID = $tr->writeDetailEntries($insertID, $dSpec);
                    cekUngu($this->db->last_query());
                    if ($insertDetailID < 1) {
                        die("Gagal saat berusaha write transaction detail entry pada " . __FILE__ . " baris " . __LINE__);
                    }
                    else {
                        $insertIDs[] = $insertDetailID;
                        $insertDeIDs[$insertID][] = $insertDetailID;
                    }
                    if ($epID != 999) {
                        $insertEpID = $tr->writeDetailEntries($epID, $dSpec);
                        if ($insertEpID < 1) {
                            die("Gagal saat berusaha write transaction detail entry point pada " . __FILE__ . " baris " . __LINE__);
                        }
                        else {
                            $insertIDs[] = $insertEpID;
                            $insertDeIDs[$epID][] = $insertEpID;
                        }
                    }

                }
                if (sizeof($insertIDs) == 0) {
                    die(lgShowAlert("Transaksi gagal disimpan karena rincian transaksi kosong."));
                }
                else {
                    $indexing_details = array();
                    foreach ($insertDeIDs as $key => $numb) {
                        $indexing_details[$key] = $numb;
                    }
                    foreach ($indexing_details as $k => $arrID) {
                        $arrBlob = blobEncode($arrID);
                        $this->db->query("UPDATE transaksi SET indexing_details = '$arrBlob' WHERE id=$k");
                        cekOrange($this->db->last_query());
                    }
                }
            }
            else {
                die(lgShowAlert("Transaksi gagal disimpan karena rincian transaksi kosong."));
            }
            if (isset($this->cCodeData[$cCode]['tableIn_detail2']) && sizeof($this->cCodeData[$cCode]['tableIn_detail2']) > 0) {
                $insertIDs = array();
                foreach ($this->cCodeData[$cCode]['tableIn_detail2'] as $dSpec) {
                    $insertIDs[] = $tr->writeDetailEntries($insertID, $dSpec);
                    if ($epID != 999) {
                        $insertIDs[] = $tr->writeDetailEntries($epID, $dSpec);
                    }
                    cekUngu($this->db->last_query());
                }
            }
            if (isset($this->cCodeData[$cCode]['tableIn_detail2_sum']) && sizeof($this->cCodeData[$cCode]['tableIn_detail2_sum']) > 0) {
                $insertIDs = array();
                foreach ($this->cCodeData[$cCode]['tableIn_detail2_sum'] as $dSpec) {
                    $insertDetailID = $tr->writeDetailEntries($insertID, $dSpec);
                    $insertIDs[] = $insertDetailID;
                    if ($epID != 999) {
                        $dd = $tr->writeDetailEntries($epID, $dSpec);
                        $insertIDs[] = $dd;
                        $mongoList['detail'][] = $dd;
                    }
                }
            }
            if (isset($this->cCodeData[$cCode]['tableIn_detail_rsltItems']) && sizeof($this->cCodeData[$cCode]['tableIn_detail_rsltItems']) > 0) {
                $insertIDs = array();
                foreach ($this->cCodeData[$cCode]['tableIn_detail_rsltItems'] as $dSpec) {
                    $dd = $tr->writeDetailEntries($insertID, $dSpec);
                    $insertIDs[] = $dd;
                    if ($epID != 999) {
                        $insertIDs[] = $tr->writeDetailEntries($epID, $dSpec);
                    }
                    cekUngu($this->db->last_query());
                }
            }
            if (isset($this->cCodeData[$cCode]['tableIn_detail_values']) && sizeof($this->cCodeData[$cCode]['tableIn_detail_values']) > 0) {
                $insertIDs = array();
                foreach ($this->cCodeData[$cCode]['tableIn_detail_values'] as $pID => $dSpec) {
                    if (isset($this->configValuesModul[$this->jenisTr]["tableIn"]['detailValues'])) {
                        foreach ($this->configValuesModul[$this->jenisTr]["tableIn"]['detailValues'] as $key => $src) {
                            if (isset($this->cCodeData[$cCode]["tableIn_detail"][$pID])) {
                                $dd = $tr->writeDetailValues($insertID, array(
                                    "produk_jenis" => $this->cCodeData[$cCode]["tableIn_detail"][$pID]['produk_jenis'],
                                    "produk_id" => $pID,
                                    "key" => $key,
                                    "value" => isset($dSpec[$src]) ? $dSpec[$src] : "0",
                                ));
                                $insertIDs[$pID][] = $dd;
                            }
                        }
                    }
                }
                if (sizeof($insertIDs) > 0) {
                    $arrBlob = blobEncode($insertIDs);
                    $this->db->query("UPDATE transaksi SET indexing_detail_values = '$arrBlob' WHERE id=$insertID");
                }
            }
            if (isset($this->cCodeData[$cCode]['tableIn_detail_values2_sum']) && sizeof($this->cCodeData[$cCode]['tableIn_detail_values2_sum']) > 0) {
                foreach ($this->cCodeData[$cCode]['tableIn_detail_values2_sum'] as $pID => $dSpec) {
                    if (isset($this->configValuesModul[$this->jenisTr]["tableIn"]['detailValues2_sum'])) {
                        $insertIDs = array();
                        foreach ($this->configValuesModul[$this->jenisTr]["tableIn"]['detailValues2_sum'] as $key => $src) {
                            $dd = $tr->writeDetailValues($insertID, array(
                                "produk_jenis" => $this->cCodeData[$cCode]['tableIn_detail2_sum'][$pID]['produk_jenis'],
                                "produk_id" => $pID,
                                "key" => $key,
                                "value" => $dSpec[$src],
                            ));
                            $insertIDs[] = $dd;
                        }
                    }
                }
            }
//        $steps = $this->configUiModul[$this->jenisTr]["steps"];

            //endregion
        }
        else {
            $insertID = "523786";
            $insertNum = "999.-1.56";
        }


        // PRE-PROCC
        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            // PRE-PROCC (karena mengeluarkan stok)
            //region pre-processors (item)
            $iterator = $preProcessor["detail"];
            if (sizeof($iterator) > 0) {
//            $itemNumLabels = isset($this->configUiModul[$this->jenisTr]['shoppingCartNumFields']) ? $this->configUiModul[$this->jenisTr]['shoppingCartNumFields'] : array();
                $itemNumLabels = array();
                cekHere("ITEM NUM LABELS");
                if (sizeof($iterator) > 0) {
                    foreach ($iterator as $cCtr => $tComSpec) {
                        $comName = $tComSpec['comName'];
                        $srcGateName = $tComSpec['srcGateName'];
                        $srcRawGateName = $tComSpec['srcRawGateName'];

                        cekHere("sub-preproc: $comName, initializing values <br>");
                        foreach ($this->cCodeData[$cCode][$srcGateName] as $xid => $dSpec) {
                            $tmpOutParams[$cCtr] = array();
                            $id = $xid;
                            $subParams = array();

                            if (isset($tComSpec['static'])) {
                                foreach ($tComSpec['static'] as $key => $value) {
                                    $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$id], $this->cCodeData[$cCode][$srcGateName][$id], 0);
                                    $subParams['static'][$key] = $realValue;
                                }
                                $subParams['static']["fulldate"] = date("Y-m-d");
                                $subParams['static']["dtime"] = date("Y-m-d H:i:s");
                                $subParams['static']["keterangan"] = "";
                            }

                            if (sizeof($subParams) > 0) {
                                $tmpOutParams[$cCtr][] = $subParams;
                                $comName = $tComSpec['comName'];
                                $srcGateName = $tComSpec['srcGateName'];
                                $srcRawGateName = $tComSpec['srcRawGateName'];
                                $resultParams = isset($tComSpec['resultParams']) ? $tComSpec['resultParams'] : array();

                                cekHere("sub preproc #: $comName, sending values " . __LINE__ . "<br>");

                                $mdlName = "Pre" . ucfirst($comName);
                                $this->load->model("Preprocs/" . $mdlName);
                                $m = new $mdlName($resultParams);

                                if (sizeof($tmpOutParams[$cCtr]) > 0) {
                                    $tobeExecuted = true;
                                }
                                else {
                                    $tobeExecuted = false;
                                }

                                if ($tobeExecuted) {
                                    $m->pair(0, $tmpOutParams[$cCtr]) or die("Tidak berhasil memasang  values pada pre-processor: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                                    $gotParams = $m->exec();
                                    // arrPrintWebs($gotParams);
                                    // matiHEre(__LINE__);
                                    // cekmerah("gotparams dari pre-proc $comName");
                                    // arrPrint($gotParams);
                                    // matiHEre();
                                    if (sizeof($gotParams) > 0) {//==gotParams means result from preprocessor
                                        foreach ($gotParams as $gateName => $paramSpec) {
                                            // arrPrint($paramSpec);
                                            // cekHitam($gateName);
                                            // cekBiru(":: getParams inject ke $gateName ::");
                                            if (!isset($this->cCodeData[$cCode][$gateName])) {
                                                $this->cCodeData[$cCode][$gateName] = array();
                                            }
                                            else {
                                                //                                    cekhijau("NOT building the session: $gateName");
                                            }
                                            // matiHEre($cCode);
                                            foreach ($paramSpec as $id => $gSpec) {
                                                if (!isset($this->cCodeData[$cCode][$gateName][$id])) {
                                                    $this->cCodeData[$cCode][$gateName][$id] = array();
                                                }
                                                if (isset($this->cCodeData[$cCode][$gateName][$id])) {
                                                    if (is_array($gSpec) && sizeof($gSpec) > 0) {
                                                        // matiHEre("ada");
                                                        foreach ($gSpec as $key => $val) {
                                                            cekHere(":: injecte ke $gateName, ::: $key diisi dengan $val " . __LINE__);
                                                            $this->cCodeData[$cCode][$gateName][$id][$key] = $val;
                                                            cekMerah($cCode . "[" . $gateName . "][" . $id . "][" . $key . "]=" . $val);
                                                        }
                                                    }
                                                    else {
                                                        cekMerah("bukan array");
                                                        matiHere();
                                                    }
                                                }
                                                //==inject gotParams to child gate
                                                if (isset($this->cCodeData[$cCode][$srcGateName][$id])) {
                                                    if (is_array($gSpec) && sizeof($gSpec) > 0) {

                                                        foreach ($gSpec as $key => $val) {
                                                            $this->cCodeData[$cCode][$srcGateName][$id][$key] = $val;

                                                        }
                                                    }
                                                    else {
                                                        cekMerah("bukan array");
                                                        matiHere();
                                                    }
                                                }
                                                if (sizeof($itemNumLabels) > 0) {
                                                    foreach ($itemNumLabels as $key => $label) {
                                                        if (isset($this->cCodeData[$cCode][$gateName][$id][$key])) {
                                                            $this->cCodeData[$cCode][$gateName][$id]['sub_' . $key] = ($this->cCodeData[$cCode][$gateName][$id]['jml'] * $this->cCodeData[$cCode][$gateName][$id][$key]);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else {
                                    cekBiru("sub-komponem $comName tidak memenuhi syarat untuk ditulis");
                                }
                                // matiHEre(__LINE__);
                            }
                        }
                    }
                }
                else {
                    //cekKuning("sub-preproc is not set");
                }
                // arrprintWebs($this->cCodeData[$cCode]);

                $this->load->helper("he_value_builder");
//                fillValues_he_value_builder($this->jenisTr, $this->stepNum, $this->stepNum, $this->configCoreModul[$this->jenisTr], $this->configUiModul[$this->jenisTr], $this->configValuesModul[$this->jenisTr]);
                $this->cCodeData[$cCode] = fillValuesSessionData_he_value_builder($this->jenisTr, $this->stepNum, $this->stepNum, $this->configCoreModul[$this->jenisTr], $this->configUiModul[$this->jenisTr], $this->configValuesModul[$this->jenisTr], $this->cCodeData[$cCode]["main"]["ppnFactor"], $this->cCodeData[$cCode]);
                //region injector gerbang value untuk pembatalan ppv dan selisih
                if (isset($this->cCodeData[$cCode]["revert"]["preProc"]["replacer"])) {
                    $replace = $this->cCodeData[$cCode]["revert"]["preProc"]["replacer"];
                    $tempCalculate = array(
                        "selisih" => ($this->cCodeData[$cCode]["main"]["hpp"] + $this->cCodeData[$cCode]["main"]["ppn"]) - ($this->cCodeData[$cCode]["main"]["nett"] + $this->cCodeData[$cCode]["main"]["ppv"]),
                        "hpp_nppv" => $this->cCodeData[$cCode]["main"]["hpp"],
                        "hpp_nppn" => $this->cCodeData[$cCode]["main"]["hpp"] + $this->cCodeData[$cCode]["main"]["ppn"],
                    );
                    foreach ($replace['recalculate'] as $iKey => $gate) {
                        $this->cCodeData[$cCode]["main"][$gate] = $tempCalculate[$gate];
                    }
                }
                //endregion
            }
            else {
                cekHitam("no sub-pre-processor defined. skipping preprocessor..<br>");
            }
            //endregion

            //region pre-processors (master)
            $iterator = $preProcessor["master"];
            if (sizeof($iterator) > 0) {
//            $itemNumLabels = isset($this->configUiModul[$this->jenisTr]['shoppingCartNumFields']) ? $this->configUiModul[$this->jenisTr]['shoppingCartNumFields'] : array();
                $itemNumLabels = array();
                if (sizeof($iterator) > 0) {
                    foreach ($iterator as $cCtr => $tComSpec) {
                        $comName = $tComSpec['comName'];
                        $srcGateName = $tComSpec['srcGateName'];
                        $srcRawGateName = $tComSpec['srcRawGateName'];
                        $resultParams = isset($tComSpec['resultParams']) ? $tComSpec['resultParams'] : array();
                        $subParams = array();

                        if (isset($tComSpec['static'])) {
                            foreach ($tComSpec['static'] as $key => $value) {
                                $realValue = makeValue($value, $this->cCodeData[$cCode]["main"], $this->cCodeData[$cCode]["main"], 0);
                                $subParams['static'][$key] = $realValue;
                            }
                            $subParams['static']["fulldate"] = date("Y-m-d");
                            $subParams['static']["dtime"] = date("Y-m-d H:i:s");
                            $subParams['static']["keterangan"] = "";
                        }
                        $tmpOutParams[$cCtr] = $subParams;

                        $mdlName = "Pre" . ucfirst($comName);
                        $this->load->model("Preprocs/" . $mdlName);
                        $m = new $mdlName($resultParams);

                        if (sizeof($tmpOutParams[$cCtr]) > 0) {
                            $tobeExecuted = true;
                        }
                        else {
                            $tobeExecuted = false;
                        }

                        if ($tobeExecuted) {
                            $m->pair(0, $tmpOutParams[$cCtr]) or die("Tidak berhasil memasang  values pada pre-processor: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                            $gotParams = $m->exec();

                            if (sizeof($gotParams) > 0) {//==gotParams means result from preprocessor
                                foreach ($gotParams as $gateName => $gSpec) {
                                    if (isset($this->cCodeData[$cCode]["main"])) {
                                        if (is_array($gSpec) && sizeof($gSpec) > 0) {
                                            foreach ($gSpec as $key => $val) {
                                                $this->cCodeData[$cCode]["main"][$key] = $val;
                                            }
                                        }
                                    }

                                    //==inject gotParams to child gate
                                    if (isset($this->cCodeData[$cCode]["main"])) {
                                        if (is_array($gSpec) && sizeof($gSpec) > 0) {
                                            foreach ($gSpec as $key => $val) {
                                                $this->cCodeData[$cCode]["main"][$key] = $val;
                                            }
                                        }
                                    }

                                    //cekMerah("REBUILDING VALUES..");
                                    if (sizeof($itemNumLabels) > 0) {
                                        //cekHijau("REBUILDING SUBS FOR ITEMS");
                                        foreach ($itemNumLabels as $key => $label) {
                                            //cekHere("$id === $key => $label");
                                            if (isset($this->cCodeData[$cCode]["main"][$key])) {
                                                $this->cCodeData[$cCode]["main"]['sub_' . $key] = ($this->cCodeData[$cCode]["main"]['jml'] * $this->cCodeData[$cCode]["main"][$key]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else {
                            cekBiru("sub-komponem $comName tidak memenuhi syarat untuk ditulis");
                        }
                    }
                }
                else {
                    //cekKuning("sub-preproc is not set");
                }
                $this->load->helper("he_value_builder");
//                fillValues_he_value_builder($this->jenisTr, $this->stepNum, $this->stepNum, $this->configCoreModul[$this->jenisTr], $this->configUiModul[$this->jenisTr], $this->configValuesModul[$this->jenisTr]);
                $this->cCodeData[$cCode] = fillValuesSessionData_he_value_builder($this->jenisTr, $this->stepNum, $this->stepNum, $this->configCoreModul[$this->jenisTr], $this->configUiModul[$this->jenisTr], $this->configValuesModul[$this->jenisTr], $this->cCodeData[$cCode]["main"]["ppnFactor"], $this->cCodeData[$cCode]);
            }
            else {
                cekHitam("no main-pre-processor defined. skipping preprocessor..<br>");
            }
            //endregion
        }


        // COMPONENT
        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            // COMPONENT-----
            //region processing sub-components, if in single step geser ke CLI
            $componentGate['detail'] = array();
            $componentConfig['detail'] = array();
            $iterator = $components["detail"];
            if (sizeof($iterator) > 0) {
                foreach ($iterator as $cCtr => $tComSpec) {
                    $tmpOutParams[$cCtr] = array();
                    $gg = 0;
                    $srcGateName = $tComSpec['srcGateName'];
                    if ($componentsDetailLoop == true) {
                        foreach ($this->cCodeData[$cCode][$srcGateName] as $id => $dSpec) {
                            $srcRawGateName = $tComSpec['srcRawGateName'];
                            $comName = $tComSpec['comName'];
                            if (substr($comName, 0, 1) == "{") {
                                $comName = trim($comName, "{");
                                $comName = trim($comName, "}");
                                $comName = str_replace($comName, $this->cCodeData[$cCode][$srcGateName][$id][$comName], $comName);
                            }

                            $mdlName = "$comsPrefix" . ucfirst($comName);
                            if (in_array($mdlName, $compValidators)) {//perlu validasi filter
                                $filterNeeded = true;
                            }
                            else {
                                $filterNeeded = false;
                            }
                            cekHere("sub-component: [$srcGateName] [$comsLocation] $comName, initializing values <br>");

                            $subParams = array();

                            if (isset($tComSpec['loop'])) {
                                foreach ($tComSpec['loop'] as $key => $value) {
                                    if (substr($key, 0, 1) == "{") {
                                        $key = trim($key, "{");
                                        $key = trim($key, "}");
                                        $key = str_replace($key, $this->cCodeData[$cCode][$srcGateName][$id][$key], $key);
                                    }

                                    $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$id], $this->cCodeData[$cCode][$srcGateName][$id], 0);
                                    $subParams['loop'][$key] = $realValue;

                                    if ($filterNeeded) {
                                        if ($subParams['loop'][$key] == 0) {
                                            unset($subParams['loop'][$key]);
                                        }
                                    }
                                }
                            }
                            if (isset($tComSpec['static'])) {
                                foreach ($tComSpec['static'] as $key => $value) {
                                    $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$id], $this->cCodeData[$cCode][$srcGateName][$id], 0);
                                    $subParams['static'][$key] = $realValue;
                                }
                                if (!isset($subParams['static']["transaksi_id"])) {
                                    $subParams['static']["transaksi_id"] = $insertID;
                                }
                                if (!isset($subParams['static']["transaksi_no"])) {
                                    $subParams['static']["transaksi_no"] = $insertNum;
                                }

                                $subParams['static']["fulldate"] = date("Y-m-d");
                                $subParams['static']["dtime"] = date("Y-m-d H:i:s");
                                $subParams['static']["keterangan"] = $this->cCodeData[$cCode]["main"]["keterangan"];
                                if (isset($revertedTarget) && (strlen($revertedTarget) > 1)) {
                                    $subParams['static']['reverted_target'] = $revertedTarget;
                                }
                            }
//arrPrintKuning($subParams);
                            if (sizeof($subParams) > 0) {
//                                cekhitam("subparam ada isinya");
                                if ($filterNeeded) {
                                    if (isset($subParams['loop']) && sizeof($subParams['loop']) > 0) {
                                        $tmpOutParams[$cCtr][] = $subParams;
                                    }
                                }
                                else {
                                    $tmpOutParams[$cCtr][] = $subParams;
                                }
                            }
                            else {
                                cekhitam("subparam TIDAK ada isinya");
                            }
                        }
                    }
                    else {
                        foreach ($this->cCodeData[$cCode][$srcGateName] as $id => $dSpec) {
                            if ($cCtr == $id) {
                                $srcRawGateName = $tComSpec['srcRawGateName'];
                                $comName = $tComSpec['comName'];
                                if (substr($comName, 0, 1) == "{") {
                                    $comName = trim($comName, "{");
                                    $comName = trim($comName, "}");

                                    $comName = str_replace($comName, $this->cCodeData[$cCode][$srcGateName][$id][$comName], $comName);
                                }

                                $mdlName = "$comsPrefix" . ucfirst($comName);
                                if (in_array($mdlName, $compValidators)) {//perlu validasi filter
                                    $filterNeeded = true;
                                }
                                else {
                                    $filterNeeded = false;
                                }
                                cekHere("sub-component: [$comsLocation] $comName, initializing values <br>");

                                $subParams = array();

                                if (isset($tComSpec['loop'])) {
                                    foreach ($tComSpec['loop'] as $key => $value) {

                                        if (substr($key, 0, 1) == "{") {
                                            $key = trim($key, "{");
                                            $key = trim($key, "}");

                                            $key = str_replace($key, $this->cCodeData[$cCode][$srcGateName][$id][$key], $key);
                                        }

                                        $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$id], $this->cCodeData[$cCode][$srcGateName][$id], 0);
                                        $subParams['loop'][$key] = $realValue;

                                        if ($filterNeeded) {
                                            if ($subParams['loop'][$key] == 0) {
                                                unset($subParams['loop'][$key]);
                                            }
                                        }
                                    }
                                }
                                if (isset($tComSpec['static'])) {
                                    foreach ($tComSpec['static'] as $key => $value) {
                                        $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$id], $this->cCodeData[$cCode][$srcGateName][$id], 0);
                                        $subParams['static'][$key] = $realValue;

                                    }
                                    if (!isset($subParams['static']["transaksi_id"])) {
                                        $subParams['static']["transaksi_id"] = $insertID;
                                    }
                                    if (!isset($subParams['static']["transaksi_no"])) {
                                        $subParams['static']["transaksi_no"] = $insertNum;
                                    }

                                    $subParams['static']["fulldate"] = date("Y-m-d");
                                    $subParams['static']["dtime"] = date("Y-m-d H:i:s");
                                    $subParams['static']["keterangan"] = "";
                                    if (isset($revertedTarget) && (strlen($revertedTarget) > 1)) {
                                        $subParams['static']['reverted_target'] = $revertedTarget;
                                    }
                                }

                                if (sizeof($subParams) > 0) {

                                    if ($filterNeeded) {
                                        if (isset($subParams['loop']) && sizeof($subParams['loop']) > 0) {
                                            $tmpOutParams[$cCtr][] = $subParams;
                                        }
                                    }
                                    else {
                                        $tmpOutParams[$cCtr][] = $subParams;
                                    }
                                }
                                else {
                                    cekhitam("subparam TIDAK ada isinya");
                                }
                            }
                        }
                    }

                    $componentGate['detail'][$cCtr] = $subParams;
                }
//arrPrintKuning($tmpOutParams);
                foreach ($iterator as $cCtr => $tComSpec) {
                    $srcGateName = $tComSpec['srcGateName'];
                    foreach ($this->cCodeData[$cCode][$srcGateName] as $id => $dSpec) {
                        $srcRawGateName = $tComSpec['srcRawGateName'];
                        $comName = $tComSpec['comName'];
                        if (substr($comName, 0, 1) == "{") {
                            $comName = trim($comName, "{");
                            $comName = trim($comName, "}");
                            $comName = str_replace($comName, $this->cCodeData[$cCode][$srcGateName][$id][$comName], $comName);
                        }
                    }
                    cekHere("sub component: [$comsLocation] $comName, sending values " . __LINE__ . "<br>");

                    $mdlName = "$comsPrefix" . ucfirst($comName);
                    $this->load->model("$comsLocation/" . $mdlName);
                    $m = new $mdlName();
                    //===filter value nol, jika harus difilter

                    if (sizeof($tmpOutParams[$cCtr]) > 0) {
                        $tobeExecuted = true;
                    }
                    else {
                        $tobeExecuted = false;
                    }

                    // matiHEre($tobeExecuted);
                    if ($tobeExecuted) {
                        //----- kiriman gerbang
                        if (method_exists($m, "setTableInMaster")) {
                            $m->setTableInMaster($this->cCodeData[$cCode]["tableIn_master"]);
                        }
                        if (method_exists($m, "setDetail")) {
                            $m->setDetail($this->cCodeData[$cCode][$srcGateName]);
                        }
                        if (method_exists($m, "setJenisTr")) {
                            $m->setJenisTr($this->jenisTr);
                        }
                        //----- kiriman gerbang
                        $m->pair($tmpOutParams[$cCtr]) or die("Tidak berhasil memasang  values pada komponen: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                        $m->exec() or die("Gagal saat berusaha  exec values pada komponen: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                        cekBiru($this->db->last_query());
                    }
                    else {
                        cekMerah("$comName tidak eksekusi");
                    }

                }
            }
            else {
                cekKuning("subcomponents is not set");
            }
            //endregion

            //region processing main components, if in single step
            $componentGate['master'] = array();
            $componentConfig['master'] = array();
            $iterator = $components["master"];
            if (sizeof($iterator) > 0) {
                $componentConfig['master'] = $iterator;
                $cCtr = 0;
                foreach ($iterator as $cCtr => $tComSpec) {
                    $cCtr++;
                    $comName = $tComSpec['comName'];
                    if (substr($comName, 0, 1) == "{") {
                        $comName = trim($comName, "{");
                        $comName = trim($comName, "}");
                        $comName = str_replace($comName, $this->cCodeData[$cCode]["main"][$comName], $comName);
                    }
                    $srcGateName = $tComSpec['srcGateName'];
                    $srcRawGateName = $tComSpec['srcRawGateName'];
                    cekHere("component # $cCtr: $comName<br>");


                    // arrPrint($this->cCodeData[$cCode][$srcGateName]);
                    // matiHEre(__LINE__);
                    $dSpec = $this->cCodeData[$cCode][$srcGateName];
                    $tmpOutParams = array();
                    if (isset($tComSpec['loop'])) {
                        foreach ($tComSpec['loop'] as $key => $value) {
                            if (substr($key, 0, 1) == "{") {
                                $key = trim($key, "{");
                                $key = trim($key, "}");
                                $key = str_replace($key, $this->cCodeData[$cCode]["main"][$key], $key);
                            }
                            $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName], $this->cCodeData[$cCode][$srcGateName], 0);
                            $tmpOutParams['loop'][$key] = $realValue;
                        }
                    }
                    if (isset($tComSpec['static'])) {
                        foreach ($tComSpec['static'] as $key => $value) {
                            $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName], $this->cCodeData[$cCode][$srcGateName], 0);
                            $tmpOutParams['static'][$key] = $realValue;
                        }
                        if (!isset($tmpOutParams['static']["transaksi_id"])) {
                            $tmpOutParams['static']["transaksi_id"] = $insertID;
                        }
                        if (!isset($tmpOutParams['static']["transaksi_no"])) {
                            $tmpOutParams['static']["transaksi_no"] = $insertNum;
                        }
                        $tmpOutParams['static']["urut"] = $cCtr;
                        $tmpOutParams['static']["fulldate"] = date("Y-m-d");
                        $tmpOutParams['static']["dtime"] = date("Y-m-d H:i:s");
                        $tmpOutParams['static']["keterangan"] = $this->cCodeData[$cCode]["main"]["keterangan"];
                    }

                    if (isset($tComSpec['static2'])) {
                        foreach ($tComSpec['static2'] as $key => $value) {
                            $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$cCtr], $this->cCodeData[$cCode][$srcGateName][$cCtr], 0);
                            $tmpOutParams['static2'][$key] = $realValue;
                        }
                        if (!isset($tmpOutParams['static2']["transaksi_id"])) {
                            $tmpOutParams['static2']["transaksi_id"] = $insertID;
                        }
                        if (!isset($tmpOutParams['static2']["transaksi_no"])) {
                            $tmpOutParams['static2']["transaksi_no"] = $insertNum;
                        }
                        $tmpOutParams['static2']["fulldate"] = date("Y-m-d");
                        $tmpOutParams['static2']["dtime"] = date("Y-m-d H:i:s");
                        $tmpOutParams['static2']["keterangan"] = $this->cCodeData[$cCode]["main"]["keterangan"];
                    }

                    $mdlName = "Com" . ucfirst($comName);
                    $this->load->model("Coms/" . $mdlName);
                    $m = new $mdlName();

                    //===filter value nol, jika harus difilter
                    $tobeExecuted = true;
                    if (in_array($mdlName, $compValidators)) {
                        $loopParams = isset($tmpOutParams['loop']) ? $tmpOutParams['loop'] : array();
                        if (sizeof($loopParams) > 0) {
                            foreach ($loopParams as $key => $val) {
                                cekmerah("$comName : $key = $val ");
                                if ($val == 0) {
                                    unset($tmpOutParams['loop'][$key]);
                                }
                            }
                        }
                        if (sizeof($tmpOutParams['loop']) < 1) {
                            $tobeExecuted = false;
                        }
                    }
                    if ($tobeExecuted) {
                        //----- kiriman gerbang untuk counter mutasi rekening
                        if (method_exists($m, "setTableInMaster")) {
                            $m->setTableInMaster($this->cCodeData[$cCode]["tableIn_master"]);
                        }
                        if (method_exists($m, "setMain")) {
                            $m->setMain($this->cCodeData[$cCode]["main"]);
                        }
                        if (method_exists($m, "setJenisTr")) {
                            $m->setJenisTr($this->jenisTr);
                        }
                        //----- kiriman gerbang untuk counter mutasi rekening
                        $m->pair($tmpOutParams) or die("Tidak berhasil memasang  values pada komponen: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                        $m->exec() or die("Gagal saat berusaha  exec values pada komponen: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                    }
                    $componentGate['master'][$cCtr] = $tmpOutParams;
                }
            }
            else {
                cekKuning("components is not set");
            }
            //endregion
        }


        // POST-PROCC
        $pakai_ini = 0;
        if ($pakai_ini == 1) {

            //region processing sub-post-processors, always
            $iterator = $postProcessor["detail"];
            if (sizeof($iterator) > 0) {
                foreach ($iterator as $cCtr => $tComSpec) {
                    $comName = $tComSpec['comName'];
                    $srcGateName = $tComSpec['srcGateName'];
                    $srcRawGateName = $tComSpec['srcRawGateName'];
                    cekHere("[$cCtr] sub-postProcessor: $comName, gate: $srcGateName, initializing values <br>");
                    $tmpOutParams[$cCtr] = array();
                    if (isset($this->cCodeData[$cCode][$srcGateName]) && (sizeof($this->cCodeData[$cCode][$srcGateName]) > 0)) {
                        foreach ($this->cCodeData[$cCode][$srcGateName] as $xid => $dSpec) {
                            $id = $xid;
                            $subParams = array();
                            if (isset($tComSpec['loop'])) {
                                foreach ($tComSpec['loop'] as $key => $value) {
                                    $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$id], $this->cCodeData[$cCode][$srcGateName][$id], 0);
                                    $subParams['loop'][$key] = $realValue;
                                }
                            }
                            if (isset($tComSpec['static'])) {
                                foreach ($tComSpec['static'] as $key => $value) {
                                    $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$id], $this->cCodeData[$cCode][$srcGateName][$id], 0);
                                    $subParams['static'][$key] = $realValue;
                                }
                                if (!isset($subParams['static']["transaksi_id"])) {
                                    $subParams['static']["transaksi_id"] = $insertID;
                                }
                                if (!isset($subParams['static']["transaksi_no"])) {
                                    $subParams['static']["transaksi_no"] = $insertNum;
                                }
                                $subParams['static']["fulldate"] = date("Y-m-d");
                                $subParams['static']["dtime"] = date("Y-m-d H:i:s");
                                if (isset($this->cCodeData[$cCode]['revert']['postProc']['detail'])) {
                                    $subParams['static']["reverted_target"] = $this->cCodeData[$cCode]["main"]['pihakExternID'];
                                }
                                $subParams['static']["keterangan"] = "";
                            }
                            if (sizeof($subParams) > 0) {
                                $tmpOutParams[$cCtr][] = $subParams;
                            }
                        }
                    }
                }
                foreach ($iterator as $cCtr => $tComSpec) {
                    $comName = $tComSpec['comName'];
                    $srcGateName = $tComSpec['srcGateName'];
                    $srcRawGateName = $tComSpec['srcRawGateName'];
                    if (isset($this->cCodeData[$cCode][$srcGateName])) {
                        cekHere("[$cCtr] sub-postProcessor: $comName, sending values " . __LINE__ . "<br>");
                        $mdlName = "Com" . ucfirst($comName);
                        $this->load->model("Coms/" . $mdlName);
                        $m = new $mdlName();
                        $m->pair($tmpOutParams[$cCtr]) or die("Tidak berhasil memasang  values pada post-processor: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                        $m->exec() or die("Gagal saat berusaha  exec values pada post-processor: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                        cekHitam($this->db->last_query());
                    }
                }
            }
            else {
                cekHitam("TIDAK ADA SETUP SUB-POSTPROC");
            }
            //endregion

            //region processing main-post-processors, always
            $iterator = $postProcessor["master"];
            if (sizeof($iterator) > 0) {
                foreach ($iterator as $cCtr => $tComSpec) {
                    $comName = $tComSpec['comName'];
                    $srcGateName = $tComSpec['srcGateName'];
                    $srcRawGateName = $tComSpec['srcRawGateName'];
                    cekHere("post-processor: $comName<br>LINE: " . __LINE__);

                    $dSpec = $this->cCodeData[$cCode][$srcGateName];
                    $tmpOutParams = array();
                    if (isset($tComSpec['loop'])) {
                        foreach ($tComSpec['loop'] as $key => $value) {
                            $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName], $this->cCodeData[$cCode][$srcGateName], 0);
                            $tmpOutParams['loop'][$key] = $realValue;
                        }
                    }
                    if (isset($tComSpec['static'])) {
                        foreach ($tComSpec['static'] as $key => $value) {
                            $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName], $this->cCodeData[$cCode][$srcGateName], 0);
                            $tmpOutParams['static'][$key] = $realValue;
                        }
                        if (!isset($tmpOutParams['static']["transaksi_id"])) {
                            $tmpOutParams['static']["transaksi_id"] = $insertID;
                        }
                        if (!isset($tmpOutParams['static']["transaksi_no"])) {
                            $tmpOutParams['static']["transaksi_no"] = $insertNum;
                        }
                        $tmpOutParams['static']["fulldate"] = date("Y-m-d");
                        $tmpOutParams['static']["dtime"] = date("Y-m-d H:i:s");
                        $tmpOutParams['static']["keterangan"] = "";
                    }
                    if (isset($tComSpec['static2'])) {
                        foreach ($tComSpec['static2'] as $key => $value) {
                            $realValue = makeValue($value, $this->cCodeData[$cCode][$srcGateName][$cCtr], $this->cCodeData[$cCode][$srcGateName][$cCtr], 0);
                            $tmpOutParams['static2'][$key] = $realValue;
                        }
                        if (!isset($tmpOutParams['static2']["transaksi_id"])) {
                            $tmpOutParams['static2']["transaksi_id"] = $insertID;
                        }
                        if (!isset($tmpOutParams['static2']["transaksi_no"])) {
                            $tmpOutParams['static2']["transaksi_no"] = $insertNum;
                        }

                        $tmpOutParams['static2']["fulldate"] = date("Y-m-d");
                        $tmpOutParams['static2']["dtime"] = date("Y-m-d H:i:s");
                        $tmpOutParams['static2']["keterangan"] = "";
                    }

                    //lgShowError("Ada kesalahan",);
                    $mdlName = "Com" . ucfirst($comName);
                    $this->load->model("Coms/" . $mdlName);
                    $m = new $mdlName();

                    cekBiru("kiriman komponem $comName");
                    $m->pair($tmpOutParams) or die("Tidak berhasil memasang  values pada post-processor: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                    $m->exec() or die("Gagal saat berusaha  exec values pada post-processor: $comName/" . $this->jenisTr . "/" . __FUNCTION__ . "/" . __LINE__);
                }
            }
            else {
                cekHitam("TIDAK ADA SETUP MAIN-POSTPROC");
            }
            //endregion
        }


        //region MENULIS KE REGISTRY
        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            if (isset($core['components']) && sizeof($core['components'])) {
                $jurnalIndex = $core['components'];
            }
            else {
                if (isset($this->cCodeData[$cCode]["revert"]["jurnal"]) && sizeof($this->cCodeData[$cCode]["revert"]["jurnal"]) > 0) {
                    $jurnalIndex = $this->cCodeData[$cCode]["revert"]["jurnal"];
                }
                else {
                    $jurnalIndex = array();
                }
            }
            //------------
            if (isset($this->configValuesModul[$this->jenisTr]['postProcessor'][$jenisTrTarget]) && sizeof($this->configValuesModul[$this->jenisTr]['postProcessor'][$jenisTrTarget])) {
                $jurnalPostProc = $this->configValuesModul[$this->jenisTr]['postProcessor'][$jenisTrTarget];
            }
            else {
                if (isset($this->cCodeData[$cCode]["revert"]["postProc"]) && sizeof($this->cCodeData[$cCode]["revert"]["postProc"]) > 0) {
                    $jurnalPostProc = $this->cCodeData[$cCode]["revert"]["postProc"];
                }
                else {
                    $jurnalPostProc = array();
                }
            }
            //------------
            if (isset($core['preProcessor'][$jenisTrTarget]) && sizeof($core['preProcessor'][$jenisTrTarget])) {
                $jurnalPreProc = $core['preProcessor'][$jenisTrTarget];
            }
            else {
                if (isset($this->cCodeData[$cCode]["revert"]["preProc"]) && sizeof($this->cCodeData[$cCode]["revert"]["preProc"]) > 0) {
                    $jurnalPreProc = $this->cCodeData[$cCode]["revert"]["preProc"];
                }
                else {
                    $jurnalPreProc = array();
                }
            }
            //------------
            if (isset($this->configValuesModul[$this->jenisTr]['coreBuilder'][$jenisTrTarget]) && sizeof($this->configValuesModul[$this->jenisTr]['coreBuilder'][$jenisTrTarget])) {
                $coreBuilder = $this->configValuesModul[$this->jenisTr]['coreBuilder'][$jenisTrTarget];
            }
            else {
                $coreBuilder = array();
            }
            //------------
            $baseRegistries = array(
                "main" => isset($this->cCodeData[$cCode]["main"]) ? $this->cCodeData[$cCode]["main"] : array(),
                "items" => isset($this->cCodeData[$cCode]["items"]) ? $this->cCodeData[$cCode]["items"] : array(),
                "items2" => isset($this->cCodeData[$cCode]["items2"]) ? $this->cCodeData[$cCode]["items2"] : array(),
                "items2_sum" => isset($this->cCodeData[$cCode]["items2_sum"]) ? $this->cCodeData[$cCode]["items2_sum"] : array(),
                "itemSrc" => isset($this->cCodeData[$cCode]["itemSrc"]) ? $this->cCodeData[$cCode]["itemSrc"] : array(),
                "itemSrc_sum" => isset($this->cCodeData[$cCode]["itemSrc_sum"]) ? $this->cCodeData[$cCode]["itemSrc_sum"] : array(),
                "items3" => isset($this->cCodeData[$cCode]["items3"]) ? $this->cCodeData[$cCode]["items3"] : array(),
                "items3_sum" => isset($this->cCodeData[$cCode]["items3_sum"]) ? $this->cCodeData[$cCode]["items3_sum"] : array(),
                "items4" => isset($this->cCodeData[$cCode]["items4"]) ? $this->cCodeData[$cCode]["items4"] : array(),
                "items4_sum" => isset($this->cCodeData[$cCode]["items4_sum"]) ? $this->cCodeData[$cCode]["items4_sum"] : array(),
                "items5_sum" => isset($this->cCodeData[$cCode]["items5_sum"]) ? $this->cCodeData[$cCode]["items5_sum"] : array(),
                'items6_sum' => isset($this->cCodeData[$cCode]['items6_sum']) ? $this->cCodeData[$cCode]['items6_sum'] : array(),
                'items7_sum' => isset($this->cCodeData[$cCode]['items7_sum']) ? $this->cCodeData[$cCode]['items7_sum'] : array(),
                'items8_sum' => isset($this->cCodeData[$cCode]['items8_sum']) ? $this->cCodeData[$cCode]['items8_sum'] : array(),
                'items9_sum' => isset($this->cCodeData[$cCode]['items9_sum']) ? $this->cCodeData[$cCode]['items9_sum'] : array(),
                'items10_sum' => isset($this->cCodeData[$cCode]['items10_sum']) ? $this->cCodeData[$cCode]['items10_sum'] : array(),
                'rsltItems' => isset($this->cCodeData[$cCode]['rsltItems']) ? $this->cCodeData[$cCode]['rsltItems'] : array(),
                'rsltItems2' => isset($this->cCodeData[$cCode]['rsltItems2']) ? $this->cCodeData[$cCode]['rsltItems2'] : array(),
                'rsltItems3' => isset($this->cCodeData[$cCode]['rsltItems3']) ? $this->cCodeData[$cCode]['rsltItems3'] : array(),
                "tableIn_master" => isset($this->cCodeData[$cCode]["tableIn_master"]) ? $this->cCodeData[$cCode]["tableIn_master"] : array(),
                "tableIn_detail" => isset($this->cCodeData[$cCode]["tableIn_detail"]) ? $this->cCodeData[$cCode]["tableIn_detail"] : array(),
                'tableIn_detail2_sum' => isset($this->cCodeData[$cCode]['tableIn_detail2_sum']) ? $this->cCodeData[$cCode]['tableIn_detail2_sum'] : array(),
                'tableIn_detail_rsltItems' => isset($this->cCodeData[$cCode]['tableIn_detail_rsltItems']) ? $this->cCodeData[$cCode]['tableIn_detail_rsltItems'] : array(),
                'tableIn_detail_rsltItems2' => isset($this->cCodeData[$cCode]['tableIn_detail_rsltItems2']) ? $this->cCodeData[$cCode]['tableIn_detail_rsltItems2'] : array(),
                'tableIn_master_values' => isset($this->cCodeData[$cCode]['tableIn_master_values']) ? $this->cCodeData[$cCode]['tableIn_master_values'] : array(),
                'tableIn_detail_values' => isset($this->cCodeData[$cCode]['tableIn_detail_values']) ? $this->cCodeData[$cCode]['tableIn_detail_values'] : array(),
                'tableIn_detail_values_rsltItems' => isset($this->cCodeData[$cCode]['tableIn_detail_values_rsltItems']) ? $this->cCodeData[$cCode]['tableIn_detail_values_rsltItems'] : array(),
                'tableIn_detail_values_rsltItems2' => isset($this->cCodeData[$cCode]['tableIn_detail_values_rsltItems2']) ? $this->cCodeData[$cCode]['tableIn_detail_values_rsltItems2'] : array(),
                'tableIn_detail_values2_sum' => isset($this->cCodeData[$cCode]['tableIn_detail_values2_sum']) ? $this->cCodeData[$cCode]['tableIn_detail_values2_sum'] : array(),
                'main_add_values' => isset($this->cCodeData[$cCode]['main_add_values']) ? $this->cCodeData[$cCode]['main_add_values'] : array(),
                'main_add_fields' => isset($this->cCodeData[$cCode]['main_add_fields']) ? $this->cCodeData[$cCode]['main_add_fields'] : array(),
                'main_elements' => isset($this->cCodeData[$cCode]['main_elements']) ? $this->cCodeData[$cCode]['main_elements'] : array(),
//                'items_elements' => isset($this->cCodeData[$cCode]['items_elements']) ? $this->cCodeData[$cCode]['items_elements'] : array(),
                'main_inputs' => isset($this->cCodeData[$cCode]['main_inputs']) ? $this->cCodeData[$cCode]['main_inputs'] : array(),
                'main_inputs_orig' => isset($this->cCodeData[$cCode]['main_inputs']) ? $this->cCodeData[$cCode]['main_inputs'] : array(),
                "receiptDetailFields" => isset($this->configLayoutModul[$this->jenisTr]['receiptDetailFields'][1]) ? $this->configLayoutModul[$this->jenisTr]['receiptDetailFields'][1] : array(),
                "receiptSumFields" => isset($this->configLayoutModul[$this->jenisTr]['receiptSumFields'][1]) ? $this->configLayoutModul[$this->jenisTr]['receiptSumFields'][1] : array(),
                "receiptDetailFields2" => isset($this->configLayoutModul[$this->jenisTr]['receiptDetailFields2'][1]) ? $this->configLayoutModul[$this->jenisTr]['receiptDetailFields2'][1] : array(),
                "receiptDetailSrcFields" => isset($this->configLayoutModul[$this->jenisTr]['receiptDetailSrcFields'][1]) ? $this->configLayoutModul[$this->jenisTr]['receiptDetailSrcFields'][1] : array(),
                "receiptSumFields2" => isset($this->configLayoutModul[$this->jenisTr]['receiptSumFields2'][1]) ? $this->configLayoutModul[$this->jenisTr]['receiptSumFields2'][1] : array(),
                "jurnal_index" => $jurnalIndex,
                "postProcessor" => $jurnalPostProc,
                "preProcessor" => $jurnalPreProc,
                "revert" => isset($this->cCodeData[$cCode]['revert']) ? $this->cCodeData[$cCode]['revert'] : array(),
                "items_komposisi" => isset($this->cCodeData[$cCode]['items_komposisi']) ? $this->cCodeData[$cCode]['items_komposisi'] : array(),
                "items_noapprove" => isset($this->cCodeData[$cCode]['items_noapprove']) ? $this->cCodeData[$cCode]['items_noapprove'] : array(),
                "jurnalItems" => isset($this->cCodeData[$cCode]['jurnalItems']) ? $this->cCodeData[$cCode]['jurnalItems'] : array(),
                "componentsBuilder" => isset($this->cCodeData[$cCode]['componentsBuilder']) ? $this->cCodeData[$cCode]['componentsBuilder'] : array(),
//                "itemPrice" => isset($this->cCodeData[$cCode]['itemPrice']) ? $this->cCodeData[$cCode]['itemPrice'] : array(),
//                "itemPrice_sum" => isset($this->cCodeData[$cCode]['itemPrice_sum']) ? $this->cCodeData[$cCode]['itemPrice_sum'] : array(),
//                "requiredParam" => (isset($coreRequiredParam[$this->jenisTr]) && sizeof($coreRequiredParam[$this->jenisTr]) > 0) ? $coreRequiredParam[$this->jenisTr] : array(),
                //-----
//                "coreBuilder" => $coreBuilder,
//                'diskon_event' => isset($this->cCodeData[$cCode]['diskon_event']) ? $this->cCodeData[$cCode]['diskon_event'] : array(),
//                'cashback_event' => isset($this->cCodeData[$cCode]['cashback_event']) ? $this->cCodeData[$cCode]['cashback_event'] : array(),
                //-----
            );
            $doWriteReg = $tr->writeDataRegistries($insertID, $baseRegistries) or die(lgShowError("Ada kesalahan", "Gagal saat berusaha  write base params into registries"));
            showLast_query("biru");

        }
        //endregion


        $pakai_ini = 0;
        if ($pakai_ini == 1) {

            $this->load->model("Coms/ComRekeningPembantuProduk");
            $this->load->model("Coms/ComProdukSerialNumber");
            foreach ($arrprodukIDs as $pid => $pSpec) {
                $itemID = $pid;
                $crd = New ComRekeningPembantuProduk();
                $crd->addFilter("gudang_id='$gudangID'");
                $crd->addFilter("cabang_id='$cabangID'");
                $crd->addFilter("extern_id='$itemID'");
                $crd->addFilter("periode='forever'");
                $crdTmp = $crd->lookupAll()->result();
                showLast_query("biru");
                cekBiru(count($crdTmp));
                if (sizeof($crdTmp) > 0) {
                    $qty = $crdTmp[0]->qty_debet;
                    $debet = $crdTmp[0]->debet;
                    $avg = ($qty > 0) ? $debet / $qty : 0;

                    $this->load->model("Mdls/MdlFifoAverage");
                    $ff = New MdlFifoAverage();
                    $ff->addFilter("jenis='produk'");
                    $ff->addFilter("produk_id='$itemID'");
                    $ff->addFilter("cabang_id='$cabangID'");
                    $ff->addFilter("gudang_id='$gudangID'");
                    $ffTmp = $ff->lookupAll()->result();
                    showLast_query("biru");
                    if (sizeof($ffTmp) > 0) {
                        $id_tbl = $ffTmp[0]->id;
                        $where = array(
                            "id" => $id_tbl
                        );
                        $data = array(
                            "jml" => $qty,
                            "hpp" => $avg,
                            "jml_nilai" => $debet,
                        );
                        $ff->updateData($where, $data);
                        showLast_query("orange");
                    }

                }

                if (isset($detailGate[$pid])) {
                    $jml = $detailGate[$pid]["jml"];
                    cekHere("[jml: $jml]");
//                arrPrintKuning($detailGate[$pid]);
                    for ($ii = 1; $ii <= $jml; $ii++) {
                        $anu[0] = array(
                            "loop" => array(),
                            "static" => array(
                                "jenis" => "99999",
                                "cabang_id" => "$cabangID",
                                "jumlah" => "1",
                                "produk_id" => $detailGate[$pid]["target_id"],
                                "produk_nama" => $detailGate[$pid]["name"],
                                "produk_serial_number" => "",//serial_number
                                "produk_sku" => $detailGate[$pid]["kode"],
                                "produk_sku_serial" => "",//produk_sku_serial
                                "produk_sku_part_id" => "",//produk_sku_part_id
                                "produk_sku_part_nama" => $detailGate[$pid]["kode"],//produk_sku_part_nama
                                "produk_sku_part_serial" => "",//produk_sku_part_serial
                                "oleh_id" => "$olehID",
                                "oleh_nama" => "$olehNama",
                                "supplier_id" => "$supplierID",
                                "supplier_nama" => "$supplierName",
                                "gudang_id" => "$gudangID",
                                //---------------
                                "transaksi_reference_id" => "$insertID",
                                "transaksi_reference_no" => "$insertNum",
                                "transaksi_reference_dtime" => date("Y-m-d H:i:s"),
                                "transaksi_reference_fulldate" => date("Y-m-d H:i:s"),
                                "transaksi_reference_count" => "1",
                                "transaksi_count" => "1",
                                "transaksi_jenis_count" => "1",
                                "part_keterangan" => "",
                                "transaksi_id" => "$insertID",
                                "transaksi_no" => "$insertNum",
                                "dtime" => date("Y-m-d H:i:s"),
                                "fulldate" => date("Y-m-d H:i:s"),
                            ),
                        );
                        arrPrintPink($anu);
                        $ss = New ComProdukSerialNumber();
                        $ss->pair($anu);
                        $ss->exec();
                    }


                }
            }

        }


        $pakai_ini = 0;// update tabel payment source
        if ($pakai_ini == 1) {
            foreach ($arrDataDetail as $spec) {
                $sisa = $spec["sisa"];
                $harga = $spec["harga"];
                $new_sisa = $sisa - $harga;
                $data = array(
                    "tagihan" => $new_sisa,
                    "sisa" => $new_sisa,
                    "dihapus" => $harga,
                );
                $where = array(
                    "id" => $spec["id_tbl"],
                );
                $tr = New MdlTransaksi();
                $tr->setFilters(array());
                $tr->updatePaymentSrc($where, $data);
                showLast_query("orange");

            }
        }


        $pakai_ini = 1;
        if ($pakai_ini == 1) {
            $this->load->model("Coms/ComRekeningPembantuCustomer");
            $this->load->model("Coms/ComRekeningPembantuCustomerDetail");
            $this->load->model("Coms/ComPaymentUangMuka");
            $this->load->model("Coms/ComPaymentUangMukaCustomer");
            $this->load->model("Coms/ComRekeningPembantuAntarcabang");
            $this->load->model("Coms/ComJurnal");
            $this->load->model("Coms/ComRekening");

            // CABANG
            $anu_j = array(
                "comName" => "Jurnal",
                "loop" => array(
                    "2010050" => "-" . $total_nilai,// hutang ke konsumen
                    "2040010" => $total_nilai,// hutang ke pusat
                ),
                "static" => array(
                    "cabang_id" => $cabangID,
                    "jenis" => $jenisTr,
                    "transaksi_id" => $insertID,
                    "transaksi_no" => $insertNum,
                    "dtime" => date("Y-m-d H:i:s"),
                    "fulldate" => date("Y-m-d"),
                ),
            );
            $anu_r = array(
                "comName" => "Rekening",
                "loop" => array(
                    "2010050" => "-" . $total_nilai,// hutang ke konsumen
                    "2040010" => $total_nilai,// hutang ke pusat
                ),
                "static" => array(
                    "cabang_id" => $cabangID,
                    "jenis" => $jenisTr,
                    "transaksi_id" => $insertID,
                    "transaksi_no" => $insertNum,
                    "dtime" => date("Y-m-d H:i:s"),
                    "fulldate" => date("Y-m-d"),
                ),
            );
            $anu_ac = array(
                "comName" => "Rekening",
                "loop" => array(
                    "2040010" => $total_nilai,// hutang ke pusat
                ),
                "static" => array(
                    "cabang_id" => $cabangID,
                    "jenis" => $jenisTr,
                    "extern_id" => "-1",
                    "extern_nama" => "PUSAT",
                    "transaksi_id" => $insertID,
                    "transaksi_no" => $insertNum,
                    "dtime" => date("Y-m-d H:i:s"),
                    "fulldate" => date("Y-m-d"),
                ),
            );
            $cj = New ComJurnal();
            $cj->pair($anu_j);
            $cj->exec();
            $cr = New ComRekening();
            $cr->pair($anu_r);
            $cr->exec();
            $cac = New ComRekeningPembantuAntarcabang();
            $cac->pair($anu_ac);
            $cac->exec();

            // DC/PUSAT
            $anu_j = array(
                "comName" => "Jurnal",
                "loop" => array(
                    "1010060010" => $total_nilai,// piutang cabang
                    "2010050" => $total_nilai,// hutang ke pusat
                ),
                "static" => array(
                    "cabang_id" => "-1",
                    "jenis" => $jenisTr,
                    "transaksi_id" => $insertID,
                    "transaksi_no" => $insertNum,
                    "dtime" => date("Y-m-d H:i:s"),
                    "fulldate" => date("Y-m-d"),
                ),
            );
            $anu_r = array(
                "comName" => "Rekening",
                "loop" => array(
                    "1010060010" => $total_nilai,// piutang cabang
                    "2010050" => $total_nilai,// hutang ke pusat
                ),
                "static" => array(
                    "cabang_id" => "-1",
                    "jenis" => $jenisTr,
                    "transaksi_id" => $insertID,
                    "transaksi_no" => $insertNum,
                    "dtime" => date("Y-m-d H:i:s"),
                    "fulldate" => date("Y-m-d"),
                ),
            );
            $anu_ac = array(
                "comName" => "Rekening",
                "loop" => array(
                    "1010060010" => $total_nilai,// piutang cabang
                ),
                "static" => array(
                    "cabang_id" => "-1",
                    "jenis" => $jenisTr,
                    "extern_id" => $cabangID,
                    "extern_nama" => $cabangNama,
                    "transaksi_id" => $insertID,
                    "transaksi_no" => $insertNum,
                    "dtime" => date("Y-m-d H:i:s"),
                    "fulldate" => date("Y-m-d"),
                ),
            );
            $cj = New ComJurnal();
            $cj->pair($anu_j);
            $cj->exec();
            $cr = New ComRekening();
            $cr->pair($anu_r);
            $cr->exec();
            $cac = New ComRekeningPembantuAntarcabang();
            $cac->pair($anu_ac);
            $cac->exec();

            foreach ($arrDataDetail as $cus_id => $cusSpec) {
                // mengurangi um relasi so, penjualan tunai
                $anu_1 = array(
                    "comName" => "RekeningPembantuCustomer",
                    "loop" => array(
                        "2010050" => "-" . $cusSpec["harga"],// hutang ke konsumen
                    ),
                    "static" => array(
                        "cabang_id" => $cabangID,
                        "extern_id" => "2010050050",
                        "extern_nama" => "Uang Muka Konsumen Tanpa Ppn",
                        "jenis" => $jenisTr,
                        "transaksi_id" => $insertID,
                        "transaksi_no" => $insertNum,
                        "dtime" => date("Y-m-d H:i:s"),
                        "fulldate" => date("Y-m-d"),
                    ),
                );
                $anu_2 = array(
                    "comName" => "RekeningPembantuCustomerDetail",
                    "loop" => array(
                        "2010050" => "-" . $cusSpec["harga"],// hutang ke konsumen
                    ),
                    "static" => array(
                        "cabang_id" => $cabangID,
                        "extern_id" => $cusSpec["id"],
                        "extern_nama" => $cusSpec["nama"],
                        "extern2_id" => "2010050050",
                        "extern2_nama" => "Uang Muka Konsumen Tanpa Ppn",
                        "jenis" => $jenisTr,
                        "transaksi_id" => $insertID,
                        "transaksi_no" => $insertNum,
                        "dtime" => date("Y-m-d H:i:s"),
                        "fulldate" => date("Y-m-d"),
                    ),
                );
                $cm = New ComRekeningPembantuCustomer();
                $cm->pair($anu_1);
                $cm->exec();
                $cmd = New ComRekeningPembantuCustomerDetail();
                $cmd->pair($anu_2);
                $cmd->exec();

                // menambah um an konsumen
                $anu_3 = array(
                    "comName" => "RekeningPembantuCustomer",
                    "loop" => array(
                        "2010050" => $cusSpec["harga"],// hutang ke konsumen Uang Muka Konsumen Tanpa Ppn
                    ),
                    "static" => array(
                        "cabang_id" => "-1",
                        "extern_id" => "2010050050",// Uang Muka Konsumen Tanpa Ppn
                        "extern_nama" => "Uang Muka Konsumen Tanpa Ppn",
                        "jenis" => $jenisTr,
                        "transaksi_id" => $insertID,
                        "transaksi_no" => $insertNum,
                        "dtime" => date("Y-m-d H:i:s"),
                        "fulldate" => date("Y-m-d"),
                    ),
                );
                $anu_4 = array(
                    "comName" => "RekeningPembantuCustomerDetail",
                    "loop" => array(
                        "2010050" => $cusSpec["harga"],// hutang ke konsumen Uang Muka Konsumen Tanpa Ppn
                    ),
                    "static" => array(
                        "cabang_id" => "-1",
                        "extern_id" => $cusSpec["id"],
                        "extern_nama" => $cusSpec["nama"],
                        "extern2_id" => "2010050050",// Uang Muka Konsumen Tanpa Ppn
                        "extern2_nama" => "Uang Muka Konsumen Tanpa Ppn",
                        "jenis" => $jenisTr,
                        "transaksi_id" => $insertID,
                        "transaksi_no" => $insertNum,
                        "dtime" => date("Y-m-d H:i:s"),
                        "fulldate" => date("Y-m-d"),
                    ),
                );
                $cm = New ComRekeningPembantuCustomer();
                $cm->pair($anu_3);
                $cm->exec();
                $cmd = New ComRekeningPembantuCustomerDetail();
                $cmd->pair($anu_4);
                $cmd->exec();

                // tabel payment um source an konsumen
                $anu_5 = array(
                    "comName" => "PaymentUangMuka",
                    "loop" => array(
                        "2010050" => "-" . $cusSpec["harga"],// hutang ke konsumen
                    ),
                    "static" => array(
                        "cabang_id" => $cabangID,
                        "cabang_nama" => $cabangNama,
                        "gudang_id" => "0",
                        "transaksi_id" => "0",
                        "extern_id" => $cusSpec["id"],
                        "extern_nama" => $cusSpec["nama"],
                        "extern2_id" => "0",
                        "extern2_nama" => "",
                        "terbayar" => $cusSpec["harga"],
                        "label" => "uang muka konsumen",
                        "extern_label2" => "customer",
                        "dtime" => date("Y-m-d H:i:s"),
                        "fulldate" => date("Y-m-d"),
                    ),
                );
                $anu_6 = array(
                    "comName" => "PaymentUangMukaCustomer",
                    "loop" => array(
                        "2010050" => "-" . $cusSpec["harga"],// hutang ke konsumen
                    ),
                    "static" => array(
                        "cabang_id" => $cabangID,
                        "cabang_nama" => $cabangNama,
                        "gudang_id" => "0",
                        "extern_id" => $cusSpec["id"],
                        "extern_nama" => $cusSpec["nama"],
                        "nilai" => $cusSpec["harga"],
                        "label" => "uang muka",
                        "extern_label2" => "customer",
                        "transaksi_id" => $insertID,
                        "transaksi_no" => $insertNum,
                        "dtime" => date("Y-m-d H:i:s"),
                        "fulldate" => date("Y-m-d"),
                    ),
                );
                $anu_7 = array(
                    "comName" => "PaymentUangMuka",
                    "loop" => array(
                        "2010050" => $cusSpec["harga"],// hutang ke konsumen
                    ),
                    "static" => array(
                        "cabang_id" => "-1",
                        "cabang_nama" => "DC/PUSAT",
                        "gudang_id" => "0",
                        "transaksi_id" => "0",
                        "extern_id" => $cusSpec["id"],
                        "extern_nama" => $cusSpec["nama"],
                        "extern2_id" => "0",
                        "extern2_nama" => "",
                        "tambah" => $cusSpec["harga"],
                        "label" => "uang muka konsumen",
                        "extern_label2" => "customer",
                        "dtime" => date("Y-m-d H:i:s"),
                        "fulldate" => date("Y-m-d"),
                    ),
                );

                $cp = New ComPaymentUangMuka();
                $cp->pair($anu_5);
                $cp->exec();
                $cpm = New ComPaymentUangMukaCustomer();
                $cpm->pair($anu_6);
                $cpm->exec();
                $cpn = New ComPaymentUangMuka();
                $cpn->pair($anu_7);
                $cpn->exec();

//                mati_disini(__LINE__ . " TESTING 1 KONSUMEN...");
            }
        }

        cekMerah(":: cek validate lajur di " . __FUNCTION__ . ", " . __FILE__);
        validateAllBalances($cabangID);


        mati_disini("---SETOP--- ADJUSTMENT BERHASIL..." . __LINE__);

        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3>DONE...</h3>");
    }

    public function cekDiskonSupplier()
    {
        $this->load->model("MdlTransaksi");
        $this->load->model("Mdls/MdlSupplierDiskon");
        $this->load->model("Mdls/MdlLockerStockDiskonVendor");
        $this->load->model("Coms/ComRekeningPembantuPiutangSupplierDetailTransItem");

        $tbl = "__rek_pembantu_subpiutangsuppliertrans__1010020030";
        $tbl1 = "_rek_pembantu_subpiutangsuppliertrans_cache";
        $tbl2 = "stock_locker_diskon";
        $jenis = "3333";
        $rekening = "1010020030";

        $this->db->select('*');
        $this->db->where("periode", "forever");
        $this->db->where("debet>", "0");
        $query = $this->db->get($tbl1)->result();
        showLast_query("biru");
        cekBiru(count($query));
        $grnIDs = array();
        $dataCache = array();
        if (sizeof($query) > 0) {
            foreach ($query as $spec) {
                $grnIDs[$spec->extern_id] = $spec->extern_id;
                $dataCache[$spec->extern3_id][$spec->extern_id][$spec->extern2_id] = $spec->debet;

            }
//            arrPrintCyan($dataCache);

            $tr = New MdlTransaksi();
            $tr->setFilters(array());
            $tr->setJointSelectFields("transaksi_id, main");
            $tr->addFilter("id in ('" . implode("','", $grnIDs) . "')");
            $trTmp = $tr->lookupDataRegistries_joined();
            showLast_query("kuning");
            arrPrint($trTmp);

        }
    }


    public function patchSOLunas()
    {
        $this->load->model("MdlTransaksi");
        $tabel = "transaksi";
        $tabel_src = "transaksi_payment_source";
        $tabel_status = "transaksi_status";
        $arrData = array();
        $tr = New MdlTransaksi();

        // penjualan tunai reguler
        $this->db->select('*');
        $this->db->from($tabel);
        $this->db->where($tabel . '.jenis', "5823so");
        $this->db->where($tabel . '.pembayaran_sys', 'cash');
        $this->db->where($tabel_src . '.sisa>', '100');
        $this->db->join($tabel_src, $tabel_src . ".transaksi_id=" . $tabel . ".id");
        $query = $this->db->get()->result();
        showLast_query("biru");
        cekBiru(count($query));
        if (sizeof($query) > 0) {
            foreach ($query as $spec) {
                $tr->setFilters(array());
                $tr->addFilter("transaksi_id=" . $spec->transaksi_id);
                $tr->setJointSelectFields("transaksi_id, main");
                $trreg = $tr->lookupDataRegistries()->result();
                $main = blobDecode($trreg[0]->main);
                $arrData[$spec->transaksi_id] = array(
                    "id_master" => $spec->id_master,
                    "jenis" => $spec->jenis,
                    "transaksi_id" => $spec->transaksi_id,
                    "nomer" => $spec->nomer,
                    "dtime" => $spec->dtime,
                    "fulldate" => $spec->fulldate,
                    "jenis_master" => $spec->jenis_master,
                    "oleh_id" => $spec->oleh_id,
                    "oleh_nama" => $spec->oleh_nama,
                    "customers_id" => $spec->customers_id,
                    "customers_nama" => $spec->customers_nama,
                    "cabang_id" => $spec->cabang_id,
                    "cabang_nama" => $spec->cabang_nama,
                    "status_id" => 10,
                    "status_nama" => "belum dibayar",
                    "transaksi_nilai" => $main["harga"],
                    "diskon_nilai" => $main["diskon_kategori_unit"],
                    "ppn_nilai" => $main["new_grand_ppn"],
                    "transaksi_net" => $main["grandTotal"],
                    "transaksi_dibayar" => 0,
                    "transaksi_reject" => 0,
                    "transaksi_fullfillment" => 0,
                    "transaksi_saldo" => $main["grandTotal"],
                    "transaksi_dikirim" => 0,
                );
            }
        }


        // penjualan tunai reguler
        $this->db->select('*');
        $this->db->from($tabel);
        $this->db->where($tabel . '.jenis', "5822so");
        $this->db->where($tabel . '.pembayaran_sys', 'cash');
        $this->db->where($tabel_src . '.sisa>', '100');
        $this->db->join($tabel_src, $tabel_src . ".transaksi_id=" . $tabel . ".id");
        $query = $this->db->get()->result();
        showLast_query("biru");
        cekBiru(count($query));
        if (sizeof($query) > 0) {
            foreach ($query as $spec) {
                $tr->setFilters(array());
                $tr->addFilter("transaksi_id=" . $spec->transaksi_id);
                $tr->setJointSelectFields("transaksi_id, main");
                $trreg = $tr->lookupDataRegistries()->result();
                $main = blobDecode($trreg[0]->main);
                $arrData[$spec->transaksi_id] = array(
                    "id_master" => $spec->id_master,
                    "jenis" => $spec->jenis,
                    "transaksi_id" => $spec->transaksi_id,
                    "nomer" => $spec->nomer,
                    "dtime" => $spec->dtime,
                    "fulldate" => $spec->fulldate,
                    "jenis_master" => $spec->jenis_master,
                    "oleh_id" => $spec->oleh_id,
                    "oleh_nama" => $spec->oleh_nama,
                    "customers_id" => $spec->customers_id,
                    "customers_nama" => $spec->customers_nama,
                    "cabang_id" => $spec->cabang_id,
                    "cabang_nama" => $spec->cabang_nama,
                    "status_id" => 10,
                    "status_nama" => "belum dibayar",
                    "transaksi_nilai" => $main["harga"],
                    "diskon_nilai" => $main["diskon_kategori_unit"],
                    "ppn_nilai" => $main["new_grand_ppn"],
                    "transaksi_net" => $main["grandTotal"],
                    "transaksi_dibayar" => 0,
                    "transaksi_reject" => 0,
                    "transaksi_fullfillment" => 0,
                    "transaksi_saldo" => $main["grandTotal"],
                    "transaksi_dikirim" => 0,
                );
            }
        }


        // penjualan credit
        $this->db->select('*');
        $this->db->from($tabel);
        $this->db->where($tabel . '.jenis', "5822spd");
        $this->db->where($tabel . '.pembayaran_sys !=', 'cash');
        $this->db->where($tabel_src . '.sisa>', '100');
        $this->db->join($tabel_src, $tabel_src . ".transaksi_id=" . $tabel . ".id");
        $query = $this->db->get()->result();
        showLast_query("kuning");
        cekKuning(count($query));
        if (sizeof($query) > 0) {
            foreach ($query as $spec) {
//                arrPrintKuning($spec);
                $ids_his = blobDecode($spec->ids_his);
                $trid_5822so = $ids_his[2]["trID"];
                $trid_5822spd = $spec->transaksi_id;

                if (!array_key_exists($trid_5822so, $arrData)) {
                    $tr->setFilters(array());
                    $tr->addFilter("id=" . $trid_5822so);
                    $trTmp = $tr->lookupAll()->result();
                    $specc = $trTmp[0];

                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $trid_5822so);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main = blobDecode($trreg[0]->main);

                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $trid_5822spd);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main_kirim = blobDecode($trreg[0]->main);

                    $arrData[$trid_5822so] = array(
                        "id_master" => $specc->id_master,
                        "jenis" => $specc->jenis,
                        "transaksi_id" => $trid_5822so,
                        "nomer" => $specc->nomer,
                        "dtime" => $specc->dtime,
                        "fulldate" => $specc->fulldate,
                        "jenis_master" => $specc->jenis_master,
                        "oleh_id" => $specc->oleh_id,
                        "oleh_nama" => $specc->oleh_nama,
                        "customers_id" => $specc->customers_id,
                        "customers_nama" => $specc->customers_nama,
                        "cabang_id" => $specc->cabang_id,
                        "cabang_nama" => $specc->cabang_nama,
                        "status_id" => 10,
                        "status_nama" => "belum dibayar",
                        "transaksi_nilai" => $main["harga"],
                        "diskon_nilai" => $main["diskon_kategori_unit"],
                        "ppn_nilai" => $main["new_grand_ppn"],
                        "transaksi_net" => $main["grandTotal"],
                        "transaksi_dibayar" => 0,
                        "transaksi_reject" => 0,
                        "transaksi_fullfillment" => 0,
                        "transaksi_saldo" => $main["grandTotal"],
                        "transaksi_dikirim" => $main_kirim["grandTotal"],
                    );
                }
                else {
                    cekMerah("SUDAH ADA DALAM ARRAY DATA...");
                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $trid_5822spd);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main_kirim = blobDecode($trreg[0]->main);

                    $kirim_sebelumnya = $arrData[$trid_5822so]["transaksi_dikirim"];
                    $kirim_new = $kirim_sebelumnya + $main_kirim["grandTotal"];
                    $arrData[$trid_5822so]["transaksi_dikirim"] = $kirim_new;
                }


//                break;
            }
        }


        // penjualan project
        $this->db->select('*');
        $this->db->from($tabel);
        $this->db->where($tabel . '.jenis', "588so");
//        $this->db->where($tabel . '.pembayaran_sys !=', 'cash');
//        $this->db->where($tabel_src . '.sisa>', '100');
//        $this->db->join($tabel_src, $tabel_src . ".transaksi_id=" . $tabel . ".id");
        $query = $this->db->get()->result();
        showLast_query("kuning");
        cekKuning(count($query));
        if (sizeof($query) > 0) {
            foreach ($query as $spec) {
//                arrPrintKuning($spec);
//                $ids_his = blobDecode($spec->ids_his);
//                $trid_5822so = $ids_his[2]["trID"];
//                $trid_5822spd = $spec->transaksi_id;

                if (!array_key_exists($spec->id, $arrData)) {
                    $specc = $spec;
//                    $tr->setFilters(array());
//                    $tr->addFilter("id=" . $trid_5822so);
//                    $trTmp = $tr->lookupAll()->result();
//                    $specc = $trTmp[0];
//
                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $specc->id);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main = blobDecode($trreg[0]->main);
//                    arrPrintWebs($main);
//
//                    $tr->setFilters(array());
//                    $tr->addFilter("transaksi_id=" . $trid_5822spd);
//                    $tr->setJointSelectFields("transaksi_id, main");
//                    $trreg = $tr->lookupDataRegistries()->result();
//                    $main_kirim = blobDecode($trreg[0]->main);

                    $arrData[$specc->id] = array(
                        "id_master" => $specc->id_master,
                        "jenis" => $specc->jenis,
                        "transaksi_id" => $specc->id,
                        "nomer" => $specc->nomer,
                        "dtime" => $specc->dtime,
                        "fulldate" => $specc->fulldate,
                        "jenis_master" => $specc->jenis_master,
                        "oleh_id" => $specc->oleh_id,
                        "oleh_nama" => $specc->oleh_nama,
                        "customers_id" => $specc->customers_id,
                        "customers_nama" => $specc->customers_nama,
                        "cabang_id" => $specc->cabang_id,
                        "cabang_nama" => $specc->cabang_nama,
                        "status_id" => 10,
                        "status_nama" => "belum dibayar",
                        "transaksi_nilai" => $main["harga_non_ppn"],
                        "diskon_nilai" => $main["diskon_kategori_unit"],
                        "ppn_nilai" => $main["new_grand_ppn"],
                        "transaksi_net" => ($main["harga_jual_project_nppn"] > 0) ? $main["harga_jual_project_nppn"] : $main["harga_non_ppn"] + $main["new_grand_ppn"],
                        "transaksi_dibayar" => 0,
                        "transaksi_reject" => 0,
                        "transaksi_fullfillment" => 0,
                        "transaksi_saldo" => ($main["harga_jual_project_nppn"] > 0) ? $main["harga_jual_project_nppn"] : $main["harga_non_ppn"] + $main["new_grand_ppn"],
                        "transaksi_dikirim" => 0,
                    );
                }
                else {
                    cekMerah("SUDAH ADA DALAM ARRAY DATA...");
                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $specc->id);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main_kirim = blobDecode($trreg[0]->main);

                    $kirim_sebelumnya = $arrData[$specc->id]["transaksi_dikirim"];
                    $kirim_new = $kirim_sebelumnya + $main_kirim["grandTotal"];
                    $arrData[$specc->id]["transaksi_dikirim"] = $kirim_new;
                }


//                break;
            }
        }


        $this->db->trans_start();


        arrPrintCyan($arrData);
        if (sizeof($arrData) > 0) {
            foreach ($arrData as $trid => $spec) {
                $anu = $this->db->insert($tabel_status, $spec);
                showLast_query("hijau");
            }
        }

//        mati_disini("---SETOP--- PATCH BERHASIL..." . __LINE__);
        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");
        cekHijau("<h3>DONE...</h3>");

    }

    public function patchPOLunas()
    {
        $this->load->model("MdlTransaksi");
        $tabel = "transaksi";
        $tabel_src = "transaksi_payment_source";
        $tabel_status = "transaksi_status";
        $arrData = array();
        $tr = New MdlTransaksi();

        $pakai_ini = 0;
        if ($pakai_ini == 1) {
            // pembelian fg 466
            $this->db->select('*');
            $this->db->from($tabel_src);
            $this->db->where($tabel_src . '.jenis', "467");
            $this->db->where($tabel_src . '.sisa>', '100');
            $this->db->join($tabel, $tabel . ".id=" . $tabel_src . ".transaksi_id");
            $query = $this->db->get()->result();
            showLast_query("biru");
            cekBiru(count($query));
            if (sizeof($query) > 0) {
                foreach ($query as $spec) {
                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $spec->transaksi_id);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main = blobDecode($trreg[0]->main);
                    $arrData[$spec->transaksi_id] = array(
                        "id_master" => $spec->id_master,
                        "jenis" => $spec->jenis,
                        "transaksi_id" => $spec->transaksi_id,
                        "nomer" => $spec->nomer,
                        "dtime" => $spec->dtime,
                        "fulldate" => $spec->fulldate,
                        "jenis_master" => $spec->jenis_master,
                        "oleh_id" => $spec->oleh_id,
                        "oleh_nama" => $spec->oleh_nama,
                        "suppliers_id" => $spec->suppliers_id,
                        "suppliers_nama" => $spec->suppliers_nama,
                        "cabang_id" => $spec->cabang_id,
                        "cabang_nama" => $spec->cabang_nama,
                        "status_id" => 10,
                        "status_nama" => "belum dibayar",
                        "transaksi_nilai" => $main["harga"],
                        "diskon_nilai" => $main["diskon_kategori_unit"],
                        "ppn_nilai" => $main["ppn"],
                        "transaksi_net" => $main["grand_total"],
                        "transaksi_dibayar" => 0,
                        "transaksi_reject" => 0,
                        "transaksi_fullfillment" => 0,
                        "transaksi_saldo" => $main["grand_total"],
                        "transaksi_dikirim" => 0,
                    );
                }
            }
            arrPrint($arrData);
        }


        // pembelian fg
        $tr = new MdlTransaksi();
        $tr->addFilter("jenis_top='466r'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("valid_qty>0");
        $sesionReplacer = array(
            "cabang_id" => "-1",
            "gudang_id" => "-1",
        );
        $akses_target = array(
            "466", "467r", "467"
        );
        $tr->addFilter("next_step_code in ('" . implode("','", $akses_target) . "')");
        $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
        showLast_query("biru");
        cekBiru(count($tmpHist));
        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $spec) {
                $idsHis = ($spec->ids_his != null) ? blobDecode($spec->ids_his) : array();
                $trid_po = $idsHis[2]["trID"];
                $trno_po = $idsHis[2]["nomer"];

                $tr->setFilters(array());
                $tr->addFilter("id=" . $trid_po);
                $trtmp = $tr->lookupAll()->result();
                $spec_po = $trtmp[0];

                $tr->setFilters(array());
                $tr->addFilter("transaksi_id=" . $trid_po);
                $tr->setJointSelectFields("transaksi_id, main");
                $trreg = $tr->lookupDataRegistries()->result();
                $main = blobDecode($trreg[0]->main);
                $arrData[$trid_po] = array(
                    "id_master" => $spec_po->id_master,
                    "jenis" => $spec_po->jenis,
                    "transaksi_id" => $trid_po,
                    "nomer" => $trno_po,
                    "dtime" => $spec_po->dtime,
                    "fulldate" => $spec_po->fulldate,
                    "jenis_master" => $spec_po->jenis_master,
                    "oleh_id" => $spec_po->oleh_id,
                    "oleh_nama" => $spec_po->oleh_nama,
                    "suppliers_id" => $spec_po->suppliers_id,
                    "suppliers_nama" => $spec_po->suppliers_nama,
                    "cabang_id" => $spec_po->cabang_id,
                    "cabang_nama" => $spec_po->cabang_nama,
                    "status_id" => 10,
                    "status_nama" => "belum dibayar",
                    "transaksi_nilai" => $main["harga"],
                    "diskon_nilai" => $main["diskon_kategori_unit"],
                    "ppn_nilai" => $main["ppn"],
                    "transaksi_net" => $main["grand_total"],
                    "transaksi_dibayar" => 0,
                    "transaksi_reject" => 0,
                    "transaksi_fullfillment" => 0,
                    "transaksi_saldo" => $main["grand_total"],
                    "transaksi_dikirim" => 0,
                );

            }
        }

        // pembelian supplies
        $tr = new MdlTransaksi();
        $tr->addFilter("jenis_top='461ro'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("valid_qty>0");
        $sesionReplacer = array(
            "cabang_id" => "-1",
            "gudang_id" => "-1",
        );
        $akses_target = array(
            "461r", "461",
        );
        $tr->addFilter("next_step_code in ('" . implode("','", $akses_target) . "')");
        $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
        showLast_query("biru");
        cekBiru(count($tmpHist));
        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $spec) {
                $idsHis = ($spec->ids_his != null) ? blobDecode($spec->ids_his) : array();
                $trid_po = $idsHis[2]["trID"];
                $trno_po = $idsHis[2]["nomer"];
                if ($trid_po > 0) {

                    $tr->setFilters(array());
                    $tr->addFilter("id=" . $trid_po);
                    $trtmp = $tr->lookupAll()->result();
                    $spec_po = $trtmp[0];

                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $trid_po);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main = blobDecode($trreg[0]->main);
                    $arrData[$trid_po] = array(
                        "id_master" => $spec_po->id_master,
                        "jenis" => $spec_po->jenis,
                        "transaksi_id" => $trid_po,
                        "nomer" => $trno_po,
                        "dtime" => $spec_po->dtime,
                        "fulldate" => $spec_po->fulldate,
                        "jenis_master" => $spec_po->jenis_master,
                        "oleh_id" => $spec_po->oleh_id,
                        "oleh_nama" => $spec_po->oleh_nama,
                        "suppliers_id" => $spec_po->suppliers_id,
                        "suppliers_nama" => $spec_po->suppliers_nama,
                        "cabang_id" => $spec_po->cabang_id,
                        "cabang_nama" => $spec_po->cabang_nama,
                        "status_id" => 10,
                        "status_nama" => "belum dibayar",
                        "transaksi_nilai" => $main["harga"],
                        "diskon_nilai" => $main["diskon_kategori_unit"],
                        "ppn_nilai" => $main["ppn"],
                        "transaksi_net" => $main["grand_total"],
                        "transaksi_dibayar" => 0,
                        "transaksi_reject" => 0,
                        "transaksi_fullfillment" => 0,
                        "transaksi_saldo" => $main["grand_total"],
                        "transaksi_dikirim" => 0,
                    );
                }

            }
        }

        // pembelian fg project
        $tr = new MdlTransaksi();
        $tr->addFilter("jenis_top='1466r'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("valid_qty>0");
        $sesionReplacer = array(
            "cabang_id" => "-1",
            "gudang_id" => "-1",
        );
        $akses_target = array(
            "1466", "1467r", "1467"
        );
        $tr->addFilter("next_step_code in ('" . implode("','", $akses_target) . "')");
        $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
        showLast_query("biru");
        cekBiru(count($tmpHist));
        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $spec) {
                $idsHis = ($spec->ids_his != null) ? blobDecode($spec->ids_his) : array();
                $trid_po = $idsHis[2]["trID"];
                $trno_po = $idsHis[2]["nomer"];
                if ($trid_po > 0) {

                    $tr->setFilters(array());
                    $tr->addFilter("id=" . $trid_po);
                    $trtmp = $tr->lookupAll()->result();
                    $spec_po = $trtmp[0];

                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $trid_po);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main = blobDecode($trreg[0]->main);
                    $arrData[$trid_po] = array(
                        "id_master" => $spec_po->id_master,
                        "jenis" => $spec_po->jenis,
                        "transaksi_id" => $trid_po,
                        "nomer" => $trno_po,
                        "dtime" => $spec_po->dtime,
                        "fulldate" => $spec_po->fulldate,
                        "jenis_master" => $spec_po->jenis_master,
                        "oleh_id" => $spec_po->oleh_id,
                        "oleh_nama" => $spec_po->oleh_nama,
                        "suppliers_id" => $spec_po->suppliers_id,
                        "suppliers_nama" => $spec_po->suppliers_nama,
                        "cabang_id" => $spec_po->cabang_id,
                        "cabang_nama" => $spec_po->cabang_nama,
                        "status_id" => 10,
                        "status_nama" => "belum dibayar",
                        "transaksi_nilai" => $main["harga"],
                        "diskon_nilai" => $main["diskon_kategori_unit"],
                        "ppn_nilai" => $main["ppn"],
                        "transaksi_net" => $main["grand_total"],
                        "transaksi_dibayar" => 0,
                        "transaksi_reject" => 0,
                        "transaksi_fullfillment" => 0,
                        "transaksi_saldo" => $main["grand_total"],
                        "transaksi_dikirim" => 0,
                    );
                }

            }
        }

        // pembelian service
        $tr = new MdlTransaksi();
        $tr->addFilter("jenis_top='463ro'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("valid_qty>0");
        $sesionReplacer = array(
            "cabang_id" => "-1",
            "gudang_id" => "-1",
        );
        $akses_target = array(
            "463o", "463",
        );
        $tr->addFilter("next_step_code in ('" . implode("','", $akses_target) . "')");
        $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
        showLast_query("biru");
        cekBiru(count($tmpHist));
        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $spec) {
                $idsHis = ($spec->ids_his != null) ? blobDecode($spec->ids_his) : array();
                $trid_po = $idsHis[2]["trID"];
                $trno_po = $idsHis[2]["nomer"];
                if ($trid_po > 0) {

                    $tr->setFilters(array());
                    $tr->addFilter("id=" . $trid_po);
                    $trtmp = $tr->lookupAll()->result();
                    $spec_po = $trtmp[0];

                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $trid_po);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main = blobDecode($trreg[0]->main);
                    $arrData[$trid_po] = array(
                        "id_master" => $spec_po->id_master,
                        "jenis" => $spec_po->jenis,
                        "transaksi_id" => $trid_po,
                        "nomer" => $trno_po,
                        "dtime" => $spec_po->dtime,
                        "fulldate" => $spec_po->fulldate,
                        "jenis_master" => $spec_po->jenis_master,
                        "oleh_id" => $spec_po->oleh_id,
                        "oleh_nama" => $spec_po->oleh_nama,
                        "suppliers_id" => $spec_po->suppliers_id,
                        "suppliers_nama" => $spec_po->suppliers_nama,
                        "cabang_id" => $spec_po->cabang_id,
                        "cabang_nama" => $spec_po->cabang_nama,
                        "status_id" => 10,
                        "status_nama" => "belum dibayar",
                        "transaksi_nilai" => $main["harga"],
                        "diskon_nilai" => $main["diskon_kategori_unit"],
                        "ppn_nilai" => $main["ppn"],
                        "transaksi_net" => $main["grand_total"],
                        "transaksi_dibayar" => 0,
                        "transaksi_reject" => 0,
                        "transaksi_fullfillment" => 0,
                        "transaksi_saldo" => $main["grand_total"],
                        "transaksi_dikirim" => 0,
                    );
                }

            }
        }

        // pembelian service project
        $tr = new MdlTransaksi();
        $tr->addFilter("jenis_top='3463ro'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("valid_qty>0");
        $sesionReplacer = array(
            "cabang_id" => "-1",
            "gudang_id" => "-1",
        );
        $akses_target = array(
            "3463o", "3463",
        );
        $tr->addFilter("next_step_code in ('" . implode("','", $akses_target) . "')");
        $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
        showLast_query("biru");
        cekBiru(count($tmpHist));
        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $spec) {
                $idsHis = ($spec->ids_his != null) ? blobDecode($spec->ids_his) : array();
                $trid_po = $idsHis[2]["trID"];
                $trno_po = $idsHis[2]["nomer"];
                if ($trid_po > 0) {

                    $tr->setFilters(array());
                    $tr->addFilter("id=" . $trid_po);
                    $trtmp = $tr->lookupAll()->result();
                    $spec_po = $trtmp[0];

                    $tr->setFilters(array());
                    $tr->addFilter("transaksi_id=" . $trid_po);
                    $tr->setJointSelectFields("transaksi_id, main");
                    $trreg = $tr->lookupDataRegistries()->result();
                    $main = blobDecode($trreg[0]->main);
                    $arrData[$trid_po] = array(
                        "id_master" => $spec_po->id_master,
                        "jenis" => $spec_po->jenis,
                        "transaksi_id" => $trid_po,
                        "nomer" => $trno_po,
                        "dtime" => $spec_po->dtime,
                        "fulldate" => $spec_po->fulldate,
                        "jenis_master" => $spec_po->jenis_master,
                        "oleh_id" => $spec_po->oleh_id,
                        "oleh_nama" => $spec_po->oleh_nama,
                        "suppliers_id" => $spec_po->suppliers_id,
                        "suppliers_nama" => $spec_po->suppliers_nama,
                        "cabang_id" => $spec_po->cabang_id,
                        "cabang_nama" => $spec_po->cabang_nama,
                        "status_id" => 10,
                        "status_nama" => "belum dibayar",
                        "transaksi_nilai" => $main["harga"],
                        "diskon_nilai" => $main["diskon_kategori_unit"],
                        "ppn_nilai" => $main["ppn"],
                        "transaksi_net" => $main["grand_total"],
                        "transaksi_dibayar" => 0,
                        "transaksi_reject" => 0,
                        "transaksi_fullfillment" => 0,
                        "transaksi_saldo" => $main["grand_total"],
                        "transaksi_dikirim" => 0,
                    );
                }

            }
        }

        // payment source belum lunas
        $arrJenis = array("467", "461", "1467", "463", "1463", "3463");
        $this->db->select('*');
        $this->db->from($tabel_src);
        $this->db->where_in('jenis', $arrJenis);
        $this->db->where('sisa>', '100');
        $query = $this->db->get()->result();
        showLast_query("kuning");
        cekBiru(count($query));
        if (sizeof($query) > 0) {
            foreach ($query as $spec) {
                switch ($spec->jenis) {
                    case "463":
                    case "1463":
                    case "3463":
                        $trid_po = $spec->extern3_id;
                        $trno_po = $spec->extern3_nama;
                        break;
                    case "461":
                    case "467":
                    case "1467":
                        $trid_po = $spec->extern2_id;
                        $trno_po = $spec->extern2_nama;
                        break;
                }
                if ($trid_po > 0) {
                    if (!array_key_exists($trid_po, $arrData)) {
                        $tr->setFilters(array());
                        $tr->addFilter("id=" . $trid_po);
                        $trtmp = $tr->lookupAll()->result();
                        $spec_po = $trtmp[0];

                        $tr->setFilters(array());
                        $tr->addFilter("transaksi_id=" . $trid_po);
                        $tr->setJointSelectFields("transaksi_id, main");
                        $trreg = $tr->lookupDataRegistries()->result();
                        $main = blobDecode($trreg[0]->main);
                        $arrData[$trid_po] = array(
                            "id_master" => $spec_po->id_master,
                            "jenis" => $spec_po->jenis,
                            "transaksi_id" => $trid_po,
                            "nomer" => $trno_po,
                            "dtime" => $spec_po->dtime,
                            "fulldate" => $spec_po->fulldate,
                            "jenis_master" => $spec_po->jenis_master,
                            "oleh_id" => $spec_po->oleh_id,
                            "oleh_nama" => $spec_po->oleh_nama,
                            "suppliers_id" => $spec_po->suppliers_id,
                            "suppliers_nama" => $spec_po->suppliers_nama,
                            "cabang_id" => $spec_po->cabang_id,
                            "cabang_nama" => $spec_po->cabang_nama,
                            "status_id" => 10,
                            "status_nama" => "belum dibayar",
                            "transaksi_nilai" => $main["harga"],
                            "diskon_nilai" => $main["diskon_kategori_unit"],
                            "ppn_nilai" => $main["ppn"],
                            "transaksi_net" => $main["grand_total"],
                            "transaksi_dibayar" => 0,
                            "transaksi_reject" => 0,
                            "transaksi_fullfillment" => 0,
                            "transaksi_saldo" => $main["grand_total"],
                            "transaksi_dikirim" => 0,
                        );
                    }
                }
            }
        }

//        arrPrint($arrData);
//        mati_disini(__LINE__);


        $this->db->trans_start();


        arrPrintCyan($arrData);
        if (sizeof($arrData) > 0) {
            foreach ($arrData as $trid => $spec) {
                $anu = $this->db->insert($tabel_status, $spec);
                showLast_query("hijau");
            }
        }

//        mati_disini("---SETOP--- PATCH BERHASIL..." . __LINE__);
        $this->db->trans_complete() or die("Gagal saat berusaha  commit transaction!");
        cekHijau("<h3>DONE...</h3>");

    }

    public function migrasiUangMukaProject()
    {
        $html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Migrasi Uang Muka Project</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
</head>
<body class="bg-light">
<div class="container-fluid mt-5 px-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Monitoring Migrasi Jurnal Balik Uang Muka (Started Project)</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="font-weight-bold">Filter Customer:</label>
                    <select id="filterCustomer" class="form-control">
                        <option value="">-- Semua Customer --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Filter Status Jurnal:</label>
                    <select id="filterJurnal" class="form-control">
                        <option value="">-- Semua --</option>
                        <option value="Belum">Belum Jurnal (Semua)</option>
                        <option value="Sudah">Sudah Jurnal</option>
                        <option value="URGENT">🚨 Urgent (Belum Jurnal & DP Lunas)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Filter Status DP:</label>
                    <select id="filterDP" class="form-control">
                        <option value="">-- Semua --</option>
                        <option value="DP Lunas">DP Lunas</option>
                        <option value="Ada Sisa DP">Ada Sisa DP</option>
                        <option value="Tidak Ada DP">Tidak Ada DP</option>
                    </select>
                </div>
            </div>

            <table id="tabelMigrasi" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Extern ID</th>
                        <th>Nama Customer</th>
                        <th>Project ID</th>
                        <th>Nama Project</th>
                        <th>No Transaksi (ST)</th>
                        <th>Tanggal</th>
                        <th>Debet (Started)</th>
                        <th>Status Project</th>
                        <th>Status DP</th>
                        <th>Tagihan DP</th>
                        <th>Sisa DP</th>
                        <th>Sort Order</th>
                        <th>Status Jurnal Balik</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" style="text-align:right; font-weight:bold;">Total Debet:</th>
                        <th style="font-weight:bold;">0</th>
                        <th colspan="6"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    var table = $("#tabelMigrasi").DataTable({
        "ajax": "' . base_url("ToolCek/getMigrasiUangMukaProjectData") . '",
        "columns": [
            { "data": "extern_id" },
            { "data": "extern_nama" },
            { "data": "project_id" },
            { "data": "project_nama" },
            { "data": "transaksi_no" },
            { "data": "fulldate" },
            { "data": "debet" },
            { "data": "project_status" },
            { "data": "dp_status" },
            { "data": "dp_tagihan", render: $.fn.dataTable.render.number(\',\', \'.\', 0, \'\') },
            { "data": "dp_sisa", render: $.fn.dataTable.render.number(\',\', \'.\', 0, \'\') },
            { "data": "sort_order", "visible": false },
            { "data": "status", render: function(data, type, row) {
                if (data === "Sudah") {
                    return \'<span class="badge badge-success">Sudah</span> <small class="text-muted">\' + row.keterangan + \'</small>\';
                } else if (data === "Prioritas 1") {
                    return \'<span class="badge badge-danger">Belum</span> <span class="badge badge-danger text-white">🚨 URGENT (DP Lunas)</span>\';
                } else if (data === "Prioritas 2") {
                    return \'<span class="badge badge-danger">Belum</span> <span class="badge badge-warning text-dark">🔥 PRIORITAS (Sisa DP)</span>\';
                } else {
                    return \'<span class="badge badge-danger">Belum</span>\';
                }
            }}
        ],
        "order": [[11, "asc"], [5, "desc"]],
        "pageLength": 25,
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
            
            var intVal = function ( i ) {
                return typeof i === "string" ?
                    i.replace(/[\$,]/g, "")*1 :
                    typeof i === "number" ?
                        i : 0;
            };
            
            // Total over all filtered pages
            var total = api
                .column( 6, { search: "applied" } )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
                
            // Update footer
            $( api.column( 6 ).footer() ).html(
                $.fn.dataTable.render.number(\',\', \'.\', 0, \'\').display( total )
            );
        },
        "initComplete": function(settings, json) {
            var api = this.api();
            var select = $("#filterCustomer");
            
            // Ambil unique values dari Kolom indeks 1 (Nama Customer)
            api.column(1).data().unique().sort().each(function(d, j) {
                if (d) {
                    select.append(\'<option value="\' + d + \'">\' + d + \'</option>\');
                }
            });
        }
    });

    $("#filterCustomer").on("change", function() {
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        // Kolom indeks 1 adalah Nama Customer. Pakai exact regex agar akurat
        table.column(1).search(val ? "^" + val + "$" : "", true, false).draw();
    });

    $("#filterJurnal").on("change", function() {
        var val = $(this).val();
        // Kolom indeks 12 adalah Status Jurnal Balik
        table.column(12).search(val).draw();
    });

    $("#filterDP").on("change", function() {
        var val = $(this).val();
        // Kolom indeks 8 adalah Status DP
        table.column(8).search(val).draw();
    });
});
</script>
</body>
</html>';
        echo $html;
    }

    public function getMigrasiUangMukaProjectData()
    {
        // 1. Ambil Semua Data DP dari transaksi_payment_source (key = project_id)
        $sqlDP = "SELECT project_id, tagihan, terbayar, sisa FROM `transaksi_payment_source` WHERE `project_id` > '0' AND `_key` = 'dp'";
        $dpRecords = $this->db->query($sqlDP)->result_array();
        $mapDP = array();
        foreach ($dpRecords as $dp) {
            $mapDP[$dp['project_id']] = $dp;
        }

        // 2. Ambil jembatan relasi dari project_produk (project_start_nomer = ST number -> id = project_id)
        $sqlPP = "SELECT id, project_start_nomer, status, trash, closing_status, nama FROM `project_produk` WHERE `project_start_nomer` != '' AND `project_start_nomer` IS NOT NULL";
        $ppRecords = $this->db->query($sqlPP)->result_array();
        $mapPP = array();
        foreach ($ppRecords as $pp) {
            $mapPP[$pp['project_start_nomer']] = $pp;
        }

        // 3. Ambil Data Project & Jurnal Uang Muka
        $sql = "SELECT extern_id, extern_nama, cabang_id, dtime, jenis, fulldate, transaksi_id, transaksi_no, debet, kredit, keterangan, extern2_nama
                FROM `__rek_pembantu_subcustomer__2010050`
                WHERE (jenis = 999 AND cabang_id > 0 AND extern2_nama LIKE '%Uang Muka%' AND extern_id IN(
                        SELECT extern_id FROM `__rek_pembantu_subcustomer__2010050` WHERE keterangan LIKE '%STARTED PROJECT%' AND cabang_id > 0 GROUP BY extern_id
                    ))
                OR (keterangan LIKE '%STARTED PROJECT%' AND cabang_id > 0)
                ORDER BY extern_id, dtime ASC";

        $query = $this->db->query($sql)->result_array();

        $projects = array();
        $journals = array();

        foreach ($query as $row) {
            if (strpos($row['keterangan'], 'STARTED PROJECT') !== false) {
                $projects[] = $row;
            } elseif ($row['jenis'] == 999 && strpos($row['extern2_nama'], 'Uang Muka') !== false) {
                $journals[] = $row;
            }
        }

        $finalData = array();
        foreach ($projects as $p) {
            $status = 'Belum';
            $jurnal_terkait = '-';

            // Cari apakah ada jurnal yang match (extern_id sama dan nominal ada toleransi pembulatan <= 100)
            foreach ($journals as $index => $j) {
                if ($j['extern_id'] == $p['extern_id']) {
                    if (abs($j['kredit'] - $p['debet']) <= 100 || abs($j['debet'] - $p['debet']) <= 100) {
                        $status = 'Sudah';
                        $jurnal_terkait = $j['transaksi_no'];
                        unset($journals[$index]);
                        break;
                    }
                }
            }

            // Cek status DP menggunakan jembatan project_produk
            $has_dp = false;
            $dp_sisa = 0;
            $dp_tagihan = 0;
            $dp_status_html = '<span class="badge badge-secondary">Tidak Ada DP</span>';
            $project_status_html = '<span class="badge badge-secondary">Unknown</span>';
            $project_id = '-';
            $project_nama = '-';

            $st_number = $p['transaksi_no'];
            if (isset($mapPP[$st_number])) {
                $pp = $mapPP[$st_number];
                $master_project_id = $pp['id'];

                $project_id = $master_project_id;
                $project_nama = $pp['nama'];

                // Set Project Status
                if ($pp['trash'] == 1) {
                    $project_status_html = '<span class="badge badge-danger">Canceled/Dihapus</span>';
                } elseif ($pp['closing_status'] == 1) {
                    $project_status_html = '<span class="badge badge-success">Sudah Closing</span>';
                } else {
                    $project_status_html = '<span class="badge badge-primary">Aktif (Belum Closing)</span>';
                }

                if (isset($mapDP[$master_project_id])) {
                    $has_dp = true;
                    $dp_sisa = $mapDP[$master_project_id]['sisa'];
                    $dp_tagihan = $mapDP[$master_project_id]['tagihan'];
                }
            }

            if ($has_dp) {
                if ($dp_sisa < 1000) {
                    $dp_status_html = '<span class="badge badge-success">DP Lunas</span>';
                } else {
                    $dp_status_html = '<span class="badge badge-warning text-dark">Ada Sisa DP</span>';
                }
            }

            if ($status === 'Sudah') {
                $sort_order = 4; // Paling bawah
                $keterangan = 'Jurnal: ' . $jurnal_terkait;
            } else {
                if ($has_dp && $dp_sisa < 1000) {
                    $status = 'Prioritas 1';
                    $sort_order = 1; // URGENT (Paling atas)
                    $keterangan = 'Belum Dijurnal - DP Lunas';
                } elseif ($has_dp && $dp_sisa >= 1000) {
                    $status = 'Prioritas 2';
                    $sort_order = 2; // PRIORITAS
                    $keterangan = 'Belum Dijurnal - Ada Sisa DP';
                } else {
                    $status = 'Belum';
                    $sort_order = 3; // Biasa
                    $keterangan = 'Belum Dijurnal';
                }
            }

            $p['project_id'] = $project_id;
            $p['project_nama'] = $project_nama;
            $p['status'] = $status;
            $p['project_status'] = $project_status_html;
            $p['dp_status'] = $dp_status_html;
            $p['dp_tagihan'] = (float)$dp_tagihan;
            $p['dp_sisa'] = (float)$dp_sisa;
            $p['keterangan'] = $keterangan;
            $p['sort_order'] = $sort_order;
            $finalData[] = $p;
        }

        $output = array("data" => $finalData);
        echo json_encode($output);
    }

    public function debugData()
    {
        $sql1 = "SELECT * FROM `__rek_pembantu_subcustomer__2010050` WHERE keterangan LIKE '%STARTED PROJECT%' LIMIT 3";
        $res1 = $this->db->query($sql1)->result_array();

        $sql2 = "SELECT * FROM `transaksi_payment_source` WHERE project_id > 0 LIMIT 3";
        $res2 = $this->db->query($sql2)->result_array();

        echo "<pre>";
        echo "=== STARTED PROJECT ===\n";
        print_r($res1);
        echo "\n=== PAYMENT SOURCE ===\n";
        print_r($res2);
        echo "</pre>";
    }

    public function analyzeLunasBelumJurnal()
    {
        $sqlPayment = "SELECT nomer, tagihan, terbayar, sisa FROM `transaksi_payment_source` WHERE `project_id` > '0' AND `sisa` < '1000' AND `_key` = 'dp' ORDER BY sisa DESC";
        $payments = $this->db->query($sqlPayment)->result_array();

        $sqlProjects = "SELECT extern_id, extern_nama, dtime, transaksi_id, transaksi_no, debet, kredit, keterangan
                        FROM `__rek_pembantu_subcustomer__2010050`
                        WHERE keterangan LIKE '%STARTED PROJECT%' AND cabang_id > 0";
        $projects = $this->db->query($sqlProjects)->result_array();

        $sqlJournals = "SELECT extern_id, debet, kredit, transaksi_no
                        FROM `__rek_pembantu_subcustomer__2010050`
                        WHERE jenis = 999 AND cabang_id > 0 AND extern2_nama LIKE '%Uang Muka%'";
        $journals = $this->db->query($sqlJournals)->result_array();

        echo "<pre>";
        echo "=== ANALISIS DP LUNAS TAPI BELUM DIJURNAL BALIK ===\n\n";

        $countMatchBelumJurnal = 0;

        foreach ($payments as $pay) {
            $nomerSO = $pay['nomer'];
            $tagihan = $pay['tagihan'];

            foreach ($projects as $proj) {
                if (!empty($nomerSO) && strpos($proj['keterangan'], $nomerSO) !== false) {

                    // Cek apakah sudah dijurnal balik?
                    $isJurnalBalik = false;
                    foreach ($journals as $j) {
                        if ($j['extern_id'] == $proj['extern_id']) {
                            if ($j['kredit'] == $proj['debet'] || $j['debet'] == $proj['debet']) {
                                $isJurnalBalik = true;
                                break;
                            }
                        }
                    }

                    if (!$isJurnalBalik) {
                        $countMatchBelumJurnal++;
                        echo "Ditemukan Project: " . $proj['keterangan'] . "\n";
                        echo "- Customer: " . $proj['extern_nama'] . "\n";
                        echo "- Debet Started Project : " . number_format($proj['debet'], 2) . "\n";
                        echo "- Tagihan Payment Source: " . number_format($pay['tagihan'], 2) . "\n";
                        if ($proj['debet'] == $pay['tagihan']) {
                            echo ">> STATUS NILAI: COCOK (IDENTIK) ✅\n";
                        } else {
                            echo ">> STATUS NILAI: TIDAK COCOK ❌ (Selisih: ".number_format(abs($proj['debet'] - $pay['tagihan']), 2).")\n";
                        }
                        echo "--------------------------------------------------------\n";
                    }
                }
            }
        }

        echo "\nTotal DP Lunas (Sisa < 1000) yang BELUM dijurnal balik: $countMatchBelumJurnal data.\n";
        echo "</pre>";
    }

    public function checkProjectProduk()
    {
        echo "=== CHECKING project_produk ===\n";

        $sql = "SELECT * FROM `project_produk` LIMIT 1";
        $res = $this->db->query($sql)->row_array();
        echo "COLUMNS:\n";
        print_r(array_keys((array)$res));

        echo "\n\n=== SAMPLE DATA ===\n";
        print_r($res);

        // Let's take a sample 'st' number from our ledger and see if it's in project_produk
        $sql2 = "SELECT transaksi_no FROM `__rek_pembantu_subcustomer__2010050` WHERE keterangan LIKE '%STARTED PROJECT%' LIMIT 5";
        $samples = $this->db->query($sql2)->result_array();

        echo "\n\n=== CHECKING SAMPLES ===\n";
        foreach($samples as $s) {
            $st_no = $s['transaksi_no'];
            echo "Checking $st_no... \n";

            $found = false;
            foreach(array_keys((array)$res) as $col) {
                // escape col
                $check = $this->db->query("SELECT * FROM `project_produk` WHERE `$col` = ?", array($st_no))->row_array();
                if ($check) {
                    echo "FOUND in column '$col'! project_id = " . (isset($check['project_id']) ? $check['project_id'] : 'UNKNOWN') . "\n";
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                echo "NOT FOUND exactly. \n";
            }
        }
    }

    public function debugJurnal1478()
    {
        echo "<pre>";
        $sql = "SELECT extern_id, extern_nama, cabang_id, dtime, jenis, fulldate, transaksi_id, transaksi_no, debet, kredit, keterangan, extern2_nama
                FROM `__rek_pembantu_subcustomer__2010050`
                WHERE extern_id = '1478'";

        $query = $this->db->query($sql)->result_array();

        $projects = array();
        $journals = array();

        foreach ($query as $row) {
            if (strpos($row['keterangan'], 'STARTED PROJECT') !== false) {
                $projects[] = $row;
            } elseif ($row['jenis'] == 999 && strpos($row['extern2_nama'], 'Uang Muka') !== false) {
                $journals[] = $row;
            }
        }

        echo "=== PROJECTS (STARTED PROJECT) ===\n";
        print_r($projects);

        echo "\n=== JOURNALS (JENIS 999 & Uang Muka) ===\n";
        print_r($journals);

        echo "</pre>";
    }

    // START OF COMPLETE REPEATED LOGIC
    public function cekProjectTps3Way()
    {
        $filter = isset($_GET['filter']) ? trim((string)$_GET['filter']) : 'all';
        $search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
        $isJson = (isset($_GET['json']) && $_GET['json'] == 1);

        // 1. Ambil seluruh project_produk aktif (trash = 0, status = 1)
        $this->db->select("p.id, p.nama, p.harga, p.harga_nppn, p.tarif_ppn, p.ppn, p.customer_id, p.customer_nama, p.status, p.closing_status, p.trash, p.project_start_nomer, p.project_start_id, p.quot_nomer, p.quot_id, p.transaksi_id, p.transaksi_no, p.uang_muka_approved, p.garansi, p.cabang_id, p.cabang_nama");
        $this->db->from("project_produk p");
        $this->db->where("p.trash", 0);
        $this->db->where("p.status", 1);
        $this->db->order_by("p.id", "DESC");
        $projects = $this->db->get()->result_array();

        if (empty($projects)) {
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(array("status" => "empty", "data" => array()));
                return;
            }
            echo "<div style='padding:20px; font-family:sans-serif;'>Tidak ada data project aktif ditemukan.</div>";
            return;
        }

        $projIds = array();
        foreach ($projects as $p) {
            $projIds[] = (int)$p['id'];
        }

        // 2. Batch load seluruh data transaksi_payment_source terkait
        $this->db->select("id, project_id, transaksi_id, nomer, nomer_top, jenis, target_jenis, reference_jenis, _key, label, tagihan, terbayar, returned, diskon, sisa, ppn, ppn_sisa, extern_nilai2, dtime, cancel_dtime, cabang_id");
        $this->db->from("transaksi_payment_source");
        $this->db->where_in("project_id", $projIds);
        $this->db->where("(cancel_dtime IS NULL OR cancel_dtime = '0000-00-00 00:00:00')", null, false);
        $this->db->order_by("id", "ASC");
        $tpsList = $this->db->get()->result_array();

        $tpsMap7499Induk = array();
        $tpsMap749Rincian = array();
        $tpsMapOther = array();

        if (!empty($tpsList)) {
            foreach ($tpsList as $t) {
                $pId = (int)$t['project_id'];
                $target = trim((string)$t['target_jenis']);
                $jenis = trim((string)$t['jenis']);
                $label = strtolower(trim((string)$t['label']));

                // Abaikan baris internal budget / hutang operasional
                if ($label === 'budget project' || $label === 'hutang dagang' || $jenis === '3674' || $target === '3675' || $jenis === '3463' || $target === '483') {
                    continue;
                }

                // 1. Payment Source Induk (target_jenis = '7499', jenis IN ('588st','588so'))
                if ($target === '7499' && ($jenis === '588st' || $jenis === '588so' || strpos($t['nomer'], '588st') !== false || strpos($t['nomer'], '588so') !== false)) {
                    $tpsMap7499Induk[$pId][] = $t;
                }
                // 2. Payment Source Rincian Termin (target_jenis = '749' & jenis = '7499', atau retensi 7488)
                elseif (($target === '749' && $jenis === '7499') || ($target === '7488' && ($jenis === '7488' || $jenis === '7499' || $jenis === '588st')) || ($target === '749' && $jenis === '588st')) {
                    $tpsMap749Rincian[$pId][] = $t;
                } else {
                    $tpsMapOther[$pId][] = $t;
                }
            }
        }

        // 3. Batch load data Transaksi Faktur 749 & 7488 yang aktif (trash = 0, trash_4 = 0)
        $this->db->select("t.id, t.jenis, t.nomer, t.nomer_top, t.transaksi_nilai, t.ppn_nilai, t.transaksi_net, t.project_id, t.reference_id, t.reference_nomer, t.ids_ref, t.trash, t.trash_4, t.dtime, t.keterangan");
        $this->db->from("transaksi t");
        $this->db->where_in("t.jenis", array("749", "7488"));
        $this->db->where("t.trash", 0);
        $this->db->where("t.trash_4", 0);
        $this->db->where_in("t.project_id", $projIds);
        $fakturList = $this->db->get()->result_array();

        $fakturMap = array();
        $fakturTrxIds = array();
        if (!empty($fakturList)) {
            foreach ($fakturList as $fk) {
                $pId = (int)$fk['project_id'];
                $fakturMap[$pId][] = $fk;
                $fakturTrxIds[] = (int)$fk['id'];
            }
        }

        // 4. Batch load transaksi_data_values untuk nilai_bayar faktur 749 jika ada
        $dataValuesMap = array();
        if (!empty($fakturTrxIds)) {
            $this->db->select("transaksi_id, key, value");
            $this->db->from("transaksi_data_values");
            $this->db->where("key", "nilai_bayar");
            $this->db->where_in("transaksi_id", $fakturTrxIds);
            $dvList = $this->db->get()->result_array();
            if (!empty($dvList)) {
                foreach ($dvList as $dv) {
                    $trId = (int)$dv['transaksi_id'];
                    $dataValuesMap[$trId] = (float)$dv['value'];
                }
            }
        }

        // 5. Evaluasi 3-Way Matching & Anomali untuk setiap Project
        $reportList = array();
        $stats = array(
            'total' => count($projects),
            'matched' => 0,
            'anomaly_total' => 0,
            'selisih_project_vs_induk' => 0,
            'selisih_induk_vs_rincian' => 0,
            'selisih_rincian_vs_faktur' => 0,
            'sisa_out_of_sync' => 0,
            'no_tps' => 0
        );

        foreach ($projects as $p) {
            $pId = (int)$p['id'];
            $pNama = isset($p['nama']) ? $p['nama'] : "Proyek #$pId";
            $custNama = isset($p['customer_nama']) ? $p['customer_nama'] : "-";
            $startNom = isset($p['project_start_nomer']) ? $p['project_start_nomer'] : "-";
            $quotNom = isset($p['quot_nomer']) ? $p['quot_nomer'] : "-";
            $nilaiKontrakDpp = (float)$p['harga'];
            $nilaiKontrakGross = (float)$p['harga_nppn'];
            $ppnKontrak = (float)$p['ppn'];

            // Baris Induk 7499
            $activeRowsInduk = isset($tpsMap7499Induk[$pId]) ? $tpsMap7499Induk[$pId] : array();
            // Baris Rincian Termin (749 / 7488)
            $activeRowsRincian = isset($tpsMap749Rincian[$pId]) ? $tpsMap749Rincian[$pId] : array();
            // Faktur Riil 749 / 7488
            $activeFakturs = isset($fakturMap[$pId]) ? $fakturMap[$pId] : array();

            // Aggregasi Induk 7499
            $tagihanInduk = 0;
            $terbayarIndukDb = 0;
            $sisaIndukDb = 0;
            $docsInduk = array();
            $indukDetails = array();

            if (!empty($activeRowsInduk)) {
                foreach ($activeRowsInduk as $rInduk) {
                    $tagInduk = (float)$rInduk['tagihan'];
                    $terbInduk = (float)$rInduk['terbayar'];
                    $sisInduk = (float)$rInduk['sisa'];

                    $tagihanInduk += $tagInduk;
                    $terbayarIndukDb += $terbInduk;
                    $sisaIndukDb += $sisInduk;
                    $docsInduk[] = $rInduk['nomer'] . " (#" . $rInduk['id'] . ")";

                    $indukDetails[] = array(
                        'id' => (int)$rInduk['id'],
                        'transaksi_id' => (int)$rInduk['transaksi_id'],
                        'nomer' => $rInduk['nomer'],
                        'jenis' => $rInduk['jenis'],
                        'target_jenis' => $rInduk['target_jenis'],
                        'tagihan' => $tagInduk,
                        'terbayar' => $terbInduk,
                        'sisa' => $sisInduk
                    );
                }
            }

            // Aggregasi Rincian Termin & Penanganan Pola _key (Baru vs Lama)
            $totalTagihanRincian = 0;
            $totalTerbayarRincianDb = 0;
            $totalSisaRincianDb = 0;
            $rincianDetails = array();
            $hasRincianSisaMismatch = false;

            if (!empty($activeRowsRincian)) {
                foreach ($activeRowsRincian as $rRinc) {
                    $rId = (int)$rRinc['id'];
                    $rTag = (float)$rRinc['tagihan'];
                    $rTerb = (float)$rRinc['terbayar'];
                    $rSis = (float)$rRinc['sisa'];
                    $rKeyRaw = trim((string)$rRinc['_key']);
                    $rLabel = strtolower(trim((string)$rRinc['label']));
                    $rTarget = trim((string)$rRinc['target_jenis']);

                    // Deteksi Pola _key: Baru vs Lama
                    $keyType = 'termin';
                    $isLegacyKey = empty($rKeyRaw);

                    if (!empty($rKeyRaw)) {
                        $keyType = strtolower($rKeyRaw);
                    } else {
                        // Pola Transaksi Lama (Legacy _key kosong)
                        if (strpos($rLabel, 'dp') !== false || strpos($rLabel, 'uang muka') !== false || (int)$rRinc['nomer_top'] === 1) {
                            $keyType = 'dp';
                        } elseif ($rTarget === '7488' || strpos($rLabel, 'retensi') !== false) {
                            $keyType = 'retensi';
                        } else {
                            $keyType = 'termin';
                        }
                    }

                    $totalTagihanRincian += $rTag;
                    $totalTerbayarRincianDb += $rTerb;
                    $totalSisaRincianDb += $rSis;

                    // Validasi Formula Sisa Termin Rincian: sisa = tagihan - terbayar
                    $rSisSeharusnya = max(0, $rTag - $rTerb);
                    $rIsSisaMismatch = (abs($rSis - $rSisSeharusnya) > 100.0);
                    $rIsNegative = ($rSis < -10.0);

                    if ($rIsSisaMismatch || $rIsNegative) {
                        $hasRincianSisaMismatch = true;
                    }

                    $rincianDetails[] = array(
                        'id' => $rId,
                        'transaksi_id' => (int)$rRinc['transaksi_id'],
                        'nomer' => $rRinc['nomer'],
                        'nomer_top' => $rRinc['nomer_top'],
                        'key' => $keyType,
                        'is_legacy' => $isLegacyKey,
                        'label' => $rRinc['label'],
                        'target_jenis' => $rTarget,
                        'tagihan' => $rTag,
                        'terbayar' => $rTerb,
                        'sisa' => $rSis,
                        'sisa_seharusnya' => $rSisSeharusnya,
                        'is_sisa_mismatch' => $rIsSisaMismatch,
                        'is_negative' => $rIsNegative
                    );
                }
            }

            // Aggregasi Faktur Riil 749 & 7488 Terbit
            $totalFaktur749Gross = 0;
            $totalFaktur749Dpp = 0;
            $totalFaktur749Ppn = 0;
            $totalFaktur749NilaiBayar = 0;
            $fakturDetails = array();

            if (!empty($activeFakturs)) {
                foreach ($activeFakturs as $fkt) {
                    $fkId = (int)$fkt['id'];
                    $fkNet = (float)$fkt['transaksi_net'];
                    $fkNilai = (float)$fkt['transaksi_nilai'];
                    $fkPpn = (float)$fkt['ppn_nilai'];
                    $fkGross = ($fkNet > 0) ? $fkNet : $fkNilai;
                    $fkDpp = max(0, $fkGross - $fkPpn);
                    $fkNilaiBayar = isset($dataValuesMap[$fkId]) ? $dataValuesMap[$fkId] : $fkGross;

                    $totalFaktur749Gross += $fkGross;
                    $totalFaktur749Dpp += $fkDpp;
                    $totalFaktur749Ppn += $fkPpn;
                    $totalFaktur749NilaiBayar += $fkNilaiBayar;

                    $fakturDetails[] = array(
                        'id' => $fkId,
                        'jenis' => $fkt['jenis'],
                        'nomer' => $fkt['nomer'],
                        'gross' => $fkGross,
                        'dpp' => $fkDpp,
                        'ppn' => $fkPpn,
                        'nilai_bayar' => $fkNilaiBayar,
                        'dtime' => $fkt['dtime']
                    );
                }
            }

            // =========================================================================
            // VALIDASI 1: Validasi Nilai Project vs Induk Payment Source (target_jenis = '7499')
            // =========================================================================
            $selisihProjectVsInduk = $nilaiKontrakDpp - $tagihanInduk;
            $isTagihanIndukKurang = (!empty($activeRowsInduk) && ($tagihanInduk + 100.0 < $nilaiKontrakDpp));
            $isSelisihProjectVsInduk = (!empty($activeRowsInduk) && (abs($selisihProjectVsInduk) > 100.0));

            // =========================================================================
            // VALIDASI 2: Validasi Akumulasi Termin Terbit vs Induk
            // =========================================================================
            $selisihIndukVsRincian = $terbayarIndukDb - $totalTagihanRincian;
            $isSelisihIndukVsRincian = (!empty($activeRowsInduk) && (abs($selisihIndukVsRincian) > 100.0));

            // Formula Sisa Induk 7499: sisa = tagihan - terbayar
            $sisaIndukSeharusnya = max(0, $tagihanInduk - $terbayarIndukDb);
            $isSisaIndukMismatch = (!empty($activeRowsInduk) && (abs($sisaIndukDb - $sisaIndukSeharusnya) > 100.0));
            $isSisaIndukNegative = ($sisaIndukDb < -10.0);

            // =========================================================================
            // VALIDASI 3: Validasi Termin Rincian vs Invoice Riil (jenis = '749')
            // =========================================================================
            $totalInvoiceRealisasi = ($totalFaktur749Gross > 0) ? $totalFaktur749Gross : $totalTerbayarRincianDb;
            $selisihRincianVsFaktur = $totalTerbayarRincianDb - $totalFaktur749Gross;
            $isSelisihRincianVsFaktur = (!empty($activeFakturs) && (abs($selisihRincianVsFaktur) > 100.0));

            // =========================================================================
            // VALIDASI 4: Sisa / Terbayar Out of Sync
            // =========================================================================
            $isSisaOutOfSync = ($isSisaIndukMismatch || $isSisaIndukNegative || $hasRincianSisaMismatch);

            // =========================================================================
            // ANOMALY SUMMARY & DAFTAR AKSI PERBAIKAN
            // =========================================================================
            $isAnomaly = ($isSelisihProjectVsInduk || $isSelisihIndukVsRincian || $isSelisihRincianVsFaktur || $isSisaOutOfSync);
            $isMatched = (!$isAnomaly && !empty($activeRowsInduk) && $totalTagihanRincian > 0);

            // Bangun Action List & Action Queries untuk Setiap Masalah
            $rekomendasiList = array();
            $actionButtons = array();
            $allFixQueries = array();

            if (empty($activeRowsInduk) && empty($activeRowsRincian)) {
                $rekomendasiList[] = "Belum memiliki Payment Source 7499. Buat/generate alokasi termin dari transaksi SPK / SO.";
            } else {
                // 1. Aksi Masalah: Selisih Project vs Induk 7499
                if ($isSelisihProjectVsInduk && !empty($indukDetails)) {
                    $firstIndukId = (int)$indukDetails[0]['id'];
                    $qSql = "UPDATE `transaksi_payment_source` SET `tagihan` = " . number_format($nilaiKontrakDpp, 2, '.', '') . ", `sisa` = GREATEST(0, " . number_format($nilaiKontrakDpp, 2, '.', '') . " - `terbayar`) WHERE `id` = " . $firstIndukId . ";";
                    $allFixQueries[] = $qSql;

                    $rekomendasiList[] = "Update tagihan Induk 7499 (#" . $firstIndukId . ") dari Rp " . number_format($tagihanInduk) . " menjadi Rp " . number_format($nilaiKontrakDpp) . " (Selisih Rp " . number_format(abs($selisihProjectVsInduk)) . ").";
                    $actionButtons[] = array(
                        'id' => 'btn_tagihan_' . $pId,
                        'label' => 'Perbaiki Tagihan Induk',
                        'icon' => 'fa-wrench',
                        'btn_class' => 'btn-fix-item btn-warning',
                        'title' => 'Koreksi Tagihan Induk 7499 (Proyek #' . $pId . ')',
                        'description' => 'Mengubah nilai tagihan Induk 7499 (#ID: ' . $firstIndukId . ') menjadi Rp ' . number_format($nilaiKontrakDpp) . ' (sesuai Nilai Kontrak DPP Project).',
                        'action_type' => 'fix_tagihan_induk',
                        'project_id' => $pId,
                        'queries_sql' => array($qSql),
                        'payload' => array(
                            'action_type' => 'fix_tagihan_induk',
                            'project_id' => $pId,
                            'induk_id' => $firstIndukId,
                            'target_tagihan' => $nilaiKontrakDpp
                        )
                    );
                }

                // 2. Aksi Masalah: Selisih Induk vs Rincian Termin
                if ($isSelisihIndukVsRincian && !empty($indukDetails)) {
                    $firstIndukId = (int)$indukDetails[0]['id'];
                    $qSql = "UPDATE `transaksi_payment_source` SET `terbayar` = " . number_format($totalTagihanRincian, 2, '.', '') . ", `sisa` = GREATEST(0, `tagihan` - " . number_format($totalTagihanRincian, 2, '.', '') . ") WHERE `id` = " . $firstIndukId . ";";
                    $allFixQueries[] = $qSql;

                    $rekomendasiList[] = "Sinkronkan terbayar Induk 7499 dari Rp " . number_format($terbayarIndukDb) . " menjadi Rp " . number_format($totalTagihanRincian) . " (Total SUM Tagihan Rincian Termin).";
                    $actionButtons[] = array(
                        'id' => 'btn_terbayar_' . $pId,
                        'label' => 'Sinkronkan Terbayar Induk',
                        'icon' => 'fa-refresh',
                        'btn_class' => 'btn-fix-item btn-primary',
                        'title' => 'Sinkronisasi Terbayar Induk 7499 (Proyek #' . $pId . ')',
                        'description' => 'Menyesuaikan nilai terbayar Induk 7499 (#ID: ' . $firstIndukId . ') menjadi Rp ' . number_format($totalTagihanRincian) . ' (akumulasi dari ' . count($rincianDetails) . ' rincian termin) dan menghitung ulang sisa.',
                        'action_type' => 'sync_terbayar_induk',
                        'project_id' => $pId,
                        'queries_sql' => array($qSql),
                        'payload' => array(
                            'action_type' => 'sync_terbayar_induk',
                            'project_id' => $pId,
                            'induk_id' => $firstIndukId,
                            'target_terbayar' => $totalTagihanRincian
                        )
                    );
                }

                // 3. Aksi Masalah: Sisa Induk Out of Sync / Minus
                if (($isSisaIndukMismatch || $isSisaIndukNegative) && !empty($indukDetails)) {
                    $firstIndukId = (int)$indukDetails[0]['id'];
                    $qSql = "UPDATE `transaksi_payment_source` SET `sisa` = GREATEST(0, `tagihan` - `terbayar`) WHERE `id` = " . $firstIndukId . ";";
                    $allFixQueries[] = $qSql;

                    $rekomendasiList[] = "Hitung ulang sisa Induk 7499: sisa = tagihan (Rp " . number_format($tagihanInduk) . ") - terbayar (Rp " . number_format($terbayarIndukDb) . ") = Rp " . number_format($sisaIndukSeharusnya) . ".";
                    $actionButtons[] = array(
                        'id' => 'btn_sisa_induk_' . $pId,
                        'label' => 'Hitung Ulang Sisa Induk',
                        'icon' => 'fa-calculator',
                        'btn_class' => 'btn-fix-item btn-purple',
                        'title' => 'Koreksi Formula Sisa Induk 7499 (Proyek #' . $pId . ')',
                        'description' => 'Menghitung ulang sisa pada Induk 7499 (#ID: ' . $firstIndukId . '): sisa = tagihan - terbayar.',
                        'action_type' => 'recalc_sisa_induk',
                        'project_id' => $pId,
                        'queries_sql' => array($qSql),
                        'payload' => array(
                            'action_type' => 'recalc_sisa_induk',
                            'project_id' => $pId,
                            'induk_id' => $firstIndukId
                        )
                    );
                }

                // 4. Aksi Masalah: Sisa Rincian Termin Out of Sync
                if ($hasRincianSisaMismatch && !empty($rincianDetails)) {
                    $rincQueries = array();
                    $rincFixIds = array();
                    foreach ($rincianDetails as $rnc) {
                        if ($rnc['is_sisa_mismatch'] || $rnc['is_negative']) {
                            $rincFixIds[] = (int)$rnc['id'];
                            $rq = "UPDATE `transaksi_payment_source` SET `sisa` = GREATEST(0, `tagihan` - `terbayar`) WHERE `id` = " . (int)$rnc['id'] . ";";
                            $rincQueries[] = $rq;
                            $allFixQueries[] = $rq;
                        }
                    }

                    if (!empty($rincQueries)) {
                        $rekomendasiList[] = "Perbaiki formula sisa pada " . count($rincFixIds) . " baris rincian termin (sisa = tagihan - terbayar).";
                        $actionButtons[] = array(
                            'id' => 'btn_sisa_rincian_' . $pId,
                            'label' => 'Perbaiki Sisa Rincian Termin',
                            'icon' => 'fa-check-square-o',
                            'btn_class' => 'btn-fix-item btn-info',
                            'title' => 'Koreksi Sisa Baris Rincian Termin (Proyek #' . $pId . ')',
                            'description' => 'Menghitung ulang nilai sisa pada ' . count($rincFixIds) . ' baris rincian termin (#ID: ' . implode(', #', $rincFixIds) . ') agar sesuai formula tagihan - terbayar.',
                            'action_type' => 'recalc_sisa_rincian',
                            'project_id' => $pId,
                            'queries_sql' => $rincQueries,
                            'payload' => array(
                                'action_type' => 'recalc_sisa_rincian',
                                'project_id' => $pId,
                                'rincian_ids' => $rincFixIds
                            )
                        );
                    }
                }

                // Tombol Global: Perbaiki Semua Masalah Proyek Ini (Jika ada lebih dari 1 query)
                if (count($allFixQueries) > 0) {
                    $actionButtons[] = array(
                        'id' => 'btn_all_' . $pId,
                        'label' => 'Perbaiki Semua Masalah (' . count($allFixQueries) . ' Query)',
                        'icon' => 'fa-magic',
                        'btn_class' => 'btn-fix-all',
                        'title' => 'Eksekusi Seluruh Perbaikan Proyek #' . $pId,
                        'description' => 'Menjalankan seluruh langkah koreksi di atas secara otomatis dalam 1 transaksi database.',
                        'action_type' => 'fix_all_project',
                        'project_id' => $pId,
                        'queries_sql' => $allFixQueries,
                        'payload' => array(
                            'action_type' => 'fix_all_project',
                            'project_id' => $pId,
                            'queries' => $allFixQueries
                        )
                    );
                }
            }

            if (empty($rekomendasiList)) {
                $rekomendasiList[] = "Semua nilai 3-Way Match sudah sinkron dan akurat.";
            }

            // Update KPI Stats
            if (empty($activeRowsInduk) && empty($activeRowsRincian)) {
                $stats['no_tps']++;
            } elseif ($isMatched) {
                $stats['matched']++;
            } else {
                if ($isAnomaly) $stats['anomaly_total']++;
                if ($isSelisihProjectVsInduk) $stats['selisih_project_vs_induk']++;
                if ($isSelisihIndukVsRincian) $stats['selisih_induk_vs_rincian']++;
                if ($isSelisihRincianVsFaktur) $stats['selisih_rincian_vs_faktur']++;
                if ($isSisaOutOfSync) $stats['sisa_out_of_sync']++;
            }

            $projectRow = array(
                'project_id' => $pId,
                'project_nama' => $pNama,
                'customer_id' => (int)$p['customer_id'],
                'customer_nama' => $custNama,
                'cabang_nama' => isset($p['cabang_nama']) ? $p['cabang_nama'] : '-',
                'closing_status' => (int)$p['closing_status'],
                'project_start_nomer' => $startNom,
                'quot_nomer' => $quotNom,
                'nilai_kontrak_dpp' => $nilaiKontrakDpp,
                'nilai_kontrak_gross' => $nilaiKontrakGross,
                'ppn_kontrak' => $ppnKontrak,
                // Induk 7499
                'tagihan_induk' => $tagihanInduk,
                'terbayar_induk_db' => $terbayarIndukDb,
                'sisa_induk_db' => $sisaIndukDb,
                'sisa_induk_seharusnya' => $sisaIndukSeharusnya,
                'docs_induk' => implode(', ', $docsInduk),
                'induk_details' => $indukDetails,
                // Rincian Termin
                'total_tagihan_rincian' => $totalTagihanRincian,
                'total_terbayar_rincian_db' => $totalTerbayarRincianDb,
                'total_sisa_rincian_db' => $totalSisaRincianDb,
                'count_rincian' => count($activeRowsRincian),
                'rincian_details' => $rincianDetails,
                // Faktur 749 Riil
                'total_faktur_749_gross' => $totalFaktur749Gross,
                'total_faktur_749_dpp' => $totalFaktur749Dpp,
                'total_faktur_749_ppn' => $totalFaktur749Ppn,
                'count_faktur' => count($activeFakturs),
                'faktur_details' => $fakturDetails,
                // Validasi Status Flags
                'is_selisih_project_vs_induk' => $isSelisihProjectVsInduk,
                'is_tagihan_induk_kurang' => $isTagihanIndukKurang,
                'selisih_project_vs_induk' => $selisihProjectVsInduk,
                'is_selisih_induk_vs_rincian' => $isSelisihIndukVsRincian,
                'selisih_induk_vs_rincian' => $selisihIndukVsRincian,
                'is_sisa_induk_mismatch' => $isSisaIndukMismatch,
                'is_sisa_induk_negative' => $isSisaIndukNegative,
                'is_selisih_rincian_vs_faktur' => $isSelisihRincianVsFaktur,
                'selisih_rincian_vs_faktur' => $selisihRincianVsFaktur,
                'has_rincian_sisa_mismatch' => $hasRincianSisaMismatch,
                'is_sisa_out_of_sync' => $isSisaOutOfSync,
                'is_anomaly' => $isAnomaly,
                'is_matched' => $isMatched,
                'rekomendasi' => $rekomendasiList,
                'action_buttons' => $actionButtons
            );

            // Filter data
            $passFilter = true;
            if ($filter === 'anomaly') {
                $passFilter = $isAnomaly;
            } elseif ($filter === 'selisih_project_vs_induk') {
                $passFilter = $isSelisihProjectVsInduk;
            } elseif ($filter === 'selisih_induk_vs_rincian') {
                $passFilter = $isSelisihIndukVsRincian;
            } elseif ($filter === 'selisih_rincian_vs_faktur') {
                $passFilter = $isSelisihRincianVsFaktur;
            } elseif ($filter === 'sisa_out_of_sync') {
                $passFilter = $isSisaOutOfSync;
            } elseif ($filter === 'matched') {
                $passFilter = $isMatched;
            }

            if (!empty($search)) {
                $kw = strtolower($search);
                $matchKeyword = (
                    strpos((string)$pId, $kw) !== false ||
                    strpos(strtolower($pNama), $kw) !== false ||
                    strpos(strtolower($custNama), $kw) !== false ||
                    strpos(strtolower($startNom), $kw) !== false ||
                    strpos(strtolower($quotNom), $kw) !== false
                );
                if (!$matchKeyword) {
                    $passFilter = false;
                }
            }

            if ($passFilter) {
                $reportList[] = $projectRow;
            }
        }

        if ($isJson) {
            header('Content-Type: application/json');
            echo json_encode(array(
                "stats" => $stats,
                "count" => count($reportList),
                "data" => $reportList
            ));
            return;
        }

        // Render Clean Modern Interactive HTML Dashboard
        $baseUrlTool = base_url() . "ToolCek/cekProjectTps3Way";
        $executeUrl = base_url() . "ToolCek/executeFixProjectTps3Way";
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Audit & Rekonsiliasi 3-Way Matching Data Project (588 vs 7499 vs 749)</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 20px; font-size: 12px; }
                .container-fluid { max-width: 1600px; margin: 0 auto; }
                .header-panel { background: #ffffff; padding: 18px 24px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
                .header-title { font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px; }
                .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px; }
                .kpi-card { background: #ffffff; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: transform 0.15s ease; text-decoration: none; color: inherit; display: block; }
                .kpi-card:hover { transform: translateY(-2px); border-color: #cbd5e1; }
                .kpi-label { font-size: 10.5px; font-weight: 600; color: #64748b; margin-bottom: 4px; text-transform: uppercase; }
                .kpi-value { font-size: 20px; font-weight: 800; }
                .kpi-blue { border-left: 4px solid #0284c7; } .kpi-blue .kpi-value { color: #0284c7; }
                .kpi-green { border-left: 4px solid #10b981; } .kpi-green .kpi-value { color: #10b981; }
                .kpi-red { border-left: 4px solid #ef4444; } .kpi-red .kpi-value { color: #ef4444; }
                .kpi-orange { border-left: 4px solid #f97316; } .kpi-orange .kpi-value { color: #f97316; }
                .kpi-yellow { border-left: 4px solid #f59e0b; } .kpi-yellow .kpi-value { color: #f59e0b; }
                .kpi-purple { border-left: 4px solid #8b5cf6; } .kpi-purple .kpi-value { color: #8b5cf6; }
                .filter-bar { background: #ffffff; padding: 12px 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
                .filter-buttons { display: flex; gap: 6px; flex-wrap: wrap; }
                .btn-filter { padding: 6px 12px; font-size: 11px; font-weight: 600; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; }
                .btn-filter:hover, .btn-filter.active { background: #0284c7; color: #ffffff; border-color: #0284c7; }
                .search-box { display: flex; gap: 6px; align-items: center; }
                .search-input { padding: 6px 12px; font-size: 11px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; width: 220px; }
                .table-container { background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow-x: auto; }
                table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
                th { background: #f1f5f9; padding: 10px 8px; font-weight: 700; color: #334155; text-align: left; border-bottom: 2px solid #cbd5e1; white-space: nowrap; }
                td { padding: 9px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
                tr:hover td { background: #f8fafc; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .badge { padding: 3px 7px; font-size: 9.5px; font-weight: 700; border-radius: 4px; display: inline-block; white-space: nowrap; }
                .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
                .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
                .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
                .badge-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
                .badge-purple { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
                .badge-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
                .btn-raw { padding: 4px 8px; font-size: 10.5px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
                .btn-raw:hover { background: #e2e8f0; }
                .btn-toggle { cursor: pointer; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 8px; font-size: 10.5px; color: #334155; font-weight: 600; }
                .btn-toggle:hover { background: #e2e8f0; }
                .sub-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 10.5px; background: #ffffff; border-radius: 4px; overflow: hidden; border: 1px solid #e2e8f0; }
                .sub-table th { background: #e2e8f0; padding: 6px; font-size: 10px; }
                .sub-table td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; }
                .recom-box { background: #fffbeb; border: 1px solid #fef3c7; border-left: 3px solid #f59e0b; padding: 6px 10px; border-radius: 4px; font-size: 10.5px; color: #92400e; margin-top: 4px; }
                .recom-box ul { margin: 2px 0 4px 16px; padding: 0; }
                .actions-wrapper { margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px; }
                .btn-fix-item { padding: 3px 8px; font-size: 10px; font-weight: 600; border-radius: 4px; border: 1px solid transparent; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; }
                .btn-warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
                .btn-warning:hover { background: #fde68a; }
                .btn-primary { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
                .btn-primary:hover { background: #bae6fd; }
                .btn-purple { background: #f3e8ff; color: #6b21a8; border-color: #e9d5ff; }
                .btn-purple:hover { background: #e9d5ff; }
                .btn-info { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
                .btn-info:hover { background: #e2e8f0; }
                .btn-fix-all { padding: 4px 10px; font-size: 10.5px; font-weight: 700; border-radius: 4px; background: #059669; color: #ffffff; border: 1px solid #047857; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
                .btn-fix-all:hover { background: #047857; }

                /* Modal Styling */
                .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(2px); }
                .modal-content { background: #ffffff; width: 90%; max-width: 680px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; animation: modalSlide 0.2s ease-out; }
                @keyframes modalSlide { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                .modal-header { padding: 14px 18px; background: #0f172a; color: #ffffff; font-size: 13px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; }
                .modal-body { padding: 18px; max-height: 75vh; overflow-y: auto; font-size: 11.5px; }
                .modal-footer { padding: 12px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px; }
                .sql-box { background: #0f172a; color: #38bdf8; font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace; padding: 12px; border-radius: 6px; font-size: 11px; line-height: 1.5; white-space: pre-wrap; word-break: break-all; margin: 8px 0; border: 1px solid #334155; }
                .btn-modal-cancel { padding: 6px 14px; font-size: 11.5px; font-weight: 600; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; cursor: pointer; }
                .btn-modal-cancel:hover { background: #f1f5f9; }
                .btn-modal-exec { padding: 6px 16px; font-size: 11.5px; font-weight: 700; border-radius: 6px; border: 1px solid #047857; background: #059669; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
                .btn-modal-exec:hover { background: #047857; }
                .btn-modal-exec:disabled { background: #9ca3af; border-color: #9ca3af; cursor: not-allowed; }
            </style>
            <script>
                var currentFixPayload = null;

                function toggleDetail(id) {
                    var el = document.getElementById('detail-' + id);
                    var btn = document.getElementById('btn-toggle-' + id);
                    if (el.style.display === 'block') {
                        el.style.display = 'none';
                        btn.innerHTML = '<i class="fa fa-chevron-down"></i> Detail';
                    } else {
                        el.style.display = 'block';
                        btn.innerHTML = '<i class="fa fa-chevron-up"></i> Tutup';
                    }
                }

                function openFixModal(dataStr) {
                    try {
                        var data = JSON.parse(decodeURIComponent(dataStr));
                        currentFixPayload = data.payload;

                        document.getElementById('modal-title').innerHTML = '<i class="fa fa-wrench text-warning"></i> ' + data.title;
                        document.getElementById('modal-desc').innerText = data.description;

                        var sqlText = data.queries_sql.join("\n\n");
                        document.getElementById('modal-sql').innerText = sqlText;

                        document.getElementById('modal-result-box').style.display = 'none';
                        document.getElementById('modal-exec-btn').disabled = false;
                        document.getElementById('modal-exec-btn').innerHTML = '<i class="fa fa-play"></i> Konfirmasi & Eksekusi Perbaikan';
                        document.getElementById('modal-exec-btn').style.display = 'inline-flex';

                        document.getElementById('fixModal').style.display = 'flex';
                    } catch(e) {
                        alert('Gagal memuat detail aksi: ' + e.message);
                    }
                }

                function closeFixModal() {
                    document.getElementById('fixModal').style.display = 'none';
                    currentFixPayload = null;
                }

                function executeFixAction() {
                    if (!currentFixPayload) return;

                    var btn = document.getElementById('modal-exec-btn');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sedang Mengeksekusi Query...';

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo $executeUrl; ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            var resBox = document.getElementById('modal-result-box');
                            resBox.style.display = 'block';

                            if (xhr.status === 200) {
                                try {
                                    var resp = JSON.parse(xhr.responseText);
                                    if (resp.success) {
                                        resBox.className = 'recom-box';
                                        resBox.style.background = '#dcfce7';
                                        resBox.style.borderColor = '#86efac';
                                        resBox.style.color = '#15803d';
                                        resBox.innerHTML = '<strong><i class="fa fa-check-circle"></i> BERHASIL:</strong> ' + resp.message + '<br><small>Halaman akan dimuat ulang dalam 2 detik untuk memperbarui status audit...</small>';
                                        btn.style.display = 'none';
                                        setTimeout(function() {
                                            location.reload();
                                        }, 1800);
                                    } else {
                                        resBox.className = 'recom-box';
                                        resBox.style.background = '#fee2e2';
                                        resBox.style.borderColor = '#fca5a5';
                                        resBox.style.color = '#b91c1c';
                                        resBox.innerHTML = '<strong><i class="fa fa-times-circle"></i> GAGAL:</strong> ' + resp.message;
                                        btn.disabled = false;
                                        btn.innerHTML = '<i class="fa fa-repeat"></i> Coba Lagi';
                                    }
                                } catch(e) {
                                    resBox.className = 'recom-box';
                                    resBox.style.background = '#fee2e2';
                                    resBox.style.borderColor = '#fca5a5';
                                    resBox.style.color = '#b91c1c';
                                    resBox.innerHTML = '<strong>Error parsing response:</strong> ' + xhr.responseText;
                                    btn.disabled = false;
                                    btn.innerHTML = '<i class="fa fa-repeat"></i> Coba Lagi';
                                }
                            } else {
                                resBox.className = 'recom-box';
                                resBox.style.background = '#fee2e2';
                                resBox.style.borderColor = '#fca5a5';
                                resBox.style.color = '#b91c1c';
                                resBox.innerHTML = '<strong>HTTP Error ' + xhr.status + ':</strong> ' + xhr.statusText;
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fa fa-repeat"></i> Coba Lagi';
                            }
                        }
                    };
                    xhr.send(JSON.stringify(currentFixPayload));
                }
            </script>
        </head>
        <body>
        <div class="container-fluid">
            <!-- Header Panel -->
            <div class="header-panel">
                <div>
                    <div class="header-title"><i class="fa fa-balance-scale" style="color:#0284c7;"></i> Audit & Rekonsiliasi Data Project (3-Way Matching)</div>
                    <div style="font-size:11px; color:#64748b; margin-top:3px;">
                        Validasi 3 Arah: Kontrak Induk Project (<code>588st/588so</code>) &harr; Induk & Rincian Termin (<code>7499</code>) &harr; Realisasi Faktur Tagihan (<code>749/7488</code>)
                    </div>
                </div>
                <div>
                    <a href="<?php echo $baseUrlTool; ?>?json=1" target="_blank" class="btn-raw"><i class="fa fa-code"></i> Raw JSON</a>
                    <a href="<?php echo $baseUrlTool; ?>" class="btn-raw"><i class="fa fa-refresh"></i> Refresh Data</a>
                </div>
            </div>

            <!-- KPI Cards Summary -->
            <div class="kpi-grid">
                <a href="<?php echo $baseUrlTool; ?>?filter=all" class="kpi-card kpi-blue">
                    <div class="kpi-label"><i class="fa fa-folder-open"></i> Total Proyek Aktif</div>
                    <div class="kpi-value"><?php echo number_format($stats['total']); ?></div>
                </a>
                <a href="<?php echo $baseUrlTool; ?>?filter=selisih_project_vs_induk" class="kpi-card kpi-red">
                    <div class="kpi-label"><i class="fa fa-exclamation-circle"></i> 1. Project vs Induk 7499</div>
                    <div class="kpi-value"><?php echo number_format($stats['selisih_project_vs_induk']); ?></div>
                </a>
                <a href="<?php echo $baseUrlTool; ?>?filter=selisih_induk_vs_rincian" class="kpi-card kpi-orange">
                    <div class="kpi-label"><i class="fa fa-list-ol"></i> 2. Induk vs Rincian Termin</div>
                    <div class="kpi-value"><?php echo number_format($stats['selisih_induk_vs_rincian']); ?></div>
                </a>
                <a href="<?php echo $baseUrlTool; ?>?filter=selisih_rincian_vs_faktur" class="kpi-card kpi-yellow">
                    <div class="kpi-label"><i class="fa fa-file-text-o"></i> 3. Rincian vs Faktur 749</div>
                    <div class="kpi-value"><?php echo number_format($stats['selisih_rincian_vs_faktur']); ?></div>
                </a>
                <a href="<?php echo $baseUrlTool; ?>?filter=sisa_out_of_sync" class="kpi-card kpi-purple">
                    <div class="kpi-label"><i class="fa fa-calculator"></i> 4. Sisa / Terbayar Out of Sync</div>
                    <div class="kpi-value"><?php echo number_format($stats['sisa_out_of_sync']); ?></div>
                </a>
                <a href="<?php echo $baseUrlTool; ?>?filter=matched" class="kpi-card kpi-green">
                    <div class="kpi-label"><i class="fa fa-check-circle"></i> Sesuai (100% Match)</div>
                    <div class="kpi-value"><?php echo number_format($stats['matched']); ?></div>
                </a>
            </div>

            <!-- Filter & Search Bar -->
            <div class="filter-bar">
                <div class="filter-buttons">
                    <a href="<?php echo $baseUrlTool; ?>?filter=all" class="btn-filter <?php echo $filter === 'all' ? 'active' : ''; ?>">Semua Proyek (<?php echo $stats['total']; ?>)</a>
                    <a href="<?php echo $baseUrlTool; ?>?filter=anomaly" class="btn-filter <?php echo $filter === 'anomaly' ? 'active' : ''; ?>"><i class="fa fa-exclamation-triangle text-danger"></i> Ada Anomali (<?php echo $stats['anomaly_total']; ?>)</a>
                    <a href="<?php echo $baseUrlTool; ?>?filter=selisih_project_vs_induk" class="btn-filter <?php echo $filter === 'selisih_project_vs_induk' ? 'active' : ''; ?>">Selisih Project vs Induk (<?php echo $stats['selisih_project_vs_induk']; ?>)</a>
                    <a href="<?php echo $baseUrlTool; ?>?filter=selisih_induk_vs_rincian" class="btn-filter <?php echo $filter === 'selisih_induk_vs_rincian' ? 'active' : ''; ?>">Selisih Induk vs Rincian (<?php echo $stats['selisih_induk_vs_rincian']; ?>)</a>
                    <a href="<?php echo $baseUrlTool; ?>?filter=selisih_rincian_vs_faktur" class="btn-filter <?php echo $filter === 'selisih_rincian_vs_faktur' ? 'active' : ''; ?>">Selisih Rincian vs Faktur (<?php echo $stats['selisih_rincian_vs_faktur']; ?>)</a>
                    <a href="<?php echo $baseUrlTool; ?>?filter=sisa_out_of_sync" class="btn-filter <?php echo $filter === 'sisa_out_of_sync' ? 'active' : ''; ?>">Sisa Out of Sync (<?php echo $stats['sisa_out_of_sync']; ?>)</a>
                    <a href="<?php echo $baseUrlTool; ?>?filter=matched" class="btn-filter <?php echo $filter === 'matched' ? 'active' : ''; ?>"><i class="fa fa-check text-success"></i> Sesuai 100% (<?php echo $stats['matched']; ?>)</a>
                </div>
                <form method="GET" action="<?php echo $baseUrlTool; ?>" class="search-box">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <input type="text" name="search" class="search-input" placeholder="Cari ID/Customer/Nama/SPK..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-filter"><i class="fa fa-search"></i> Cari</button>
                    <?php if (!empty($search)) { ?>
                        <a href="<?php echo $baseUrlTool; ?>?filter=<?php echo htmlspecialchars($filter); ?>" class="btn-filter"><i class="fa fa-times"></i> Reset</a>
                    <?php } ?>
                </form>
            </div>

            <!-- Main Data Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;" class="text-center">ID</th>
                            <th>No SPK / SO</th>
                            <th>Nama Project & Customer</th>
                            <th class="text-right" style="color:#0284c7;">Nilai DPP Project</th>
                            <th style="background:#f0f9ff;" class="text-right">Induk 7499 (Tagihan / Terbayar)</th>
                            <th style="background:#fefce8;" class="text-right">Total Termin (SUM Rincian)</th>
                            <th style="background:#ecfdf5;" class="text-right">Total Invoice 749 Terbit</th>
                            <th class="text-center">Status Selisih (3-Way)</th>
                            <th>Detail Rekomendasi & Tombol Aksi Koreksi</th>
                            <th style="width:65px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reportList)) { ?>
                            <tr>
                                <td colspan="10" class="text-center" style="padding:35px; color:#64748b;">
                                    <i class="fa fa-info-circle fa-2x"></i><br>Tidak ada data project yang sesuai dengan kriteria filter saat ini.
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($reportList as $row) {
                                $trBg = "";
                                if ($row['is_anomaly']) {
                                    $trBg = "background:#fff1f2;";
                                } elseif ($row['is_matched']) {
                                    $trBg = "background:#f0fdf4;";
                                }
                            ?>
                                <tr style="<?php echo $trBg; ?>">
                                    <td class="text-center" style="font-weight:bold;">
                                        #<?php echo $row['project_id']; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight:700; color:#0f172a;"><?php echo htmlspecialchars($row['project_start_nomer']); ?></div>
                                        <div style="font-size:10px; color:#64748b;">SO: <strong><?php echo htmlspecialchars($row['quot_nomer']); ?></strong></div>
                                        <?php if ($row['closing_status'] == 1) { ?>
                                            <span class="badge badge-secondary" style="font-size:8.5px; margin-top:2px;">Closing</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div style="font-weight:700; color:#0f172a;"><?php echo htmlspecialchars($row['project_nama']); ?></div>
                                        <div style="font-size:10.5px; color:#475569;"><i class="fa fa-user-o"></i> <?php echo htmlspecialchars($row['customer_nama']); ?></div>
                                        <div style="font-size:9.5px; color:#64748b;">Cabang: <?php echo htmlspecialchars($row['cabang_nama']); ?></div>
                                    </td>
                                    <td class="text-right" style="font-weight:700; color:#0284c7;">
                                        Rp <?php echo number_format($row['nilai_kontrak_dpp']); ?>
                                        <div style="font-size:9.5px; color:#64748b;">Gross: Rp <?php echo number_format($row['nilai_kontrak_gross']); ?></div>
                                    </td>
                                    <!-- Induk 7499 -->
                                    <td class="text-right" style="background:#f8fafc;">
                                        <div>Tag: <strong>Rp <?php echo number_format($row['tagihan_induk']); ?></strong></div>
                                        <div style="font-size:10px; color:#059669;">Terb: Rp <?php echo number_format($row['terbayar_induk_db']); ?></div>
                                        <div style="font-size:9.5px; color:#64748b;">Sisa: Rp <?php echo number_format($row['sisa_induk_db']); ?></div>
                                        <?php if ($row['is_tagihan_induk_kurang']) { ?>
                                            <span class="badge badge-danger" title="Tagihan Induk < DPP Project"><i class="fa fa-warning"></i> Tagihan Kurang</span>
                                        <?php } ?>
                                        <?php if ($row['is_sisa_induk_negative']) { ?>
                                            <span class="badge badge-danger"><i class="fa fa-times"></i> Sisa Minus</span>
                                        <?php } ?>
                                    </td>
                                    <!-- Total Termin (Rincian) -->
                                    <td class="text-right" style="background:#fffbeb;">
                                        <div style="font-weight:700; color:#b45309;">Rp <?php echo number_format($row['total_tagihan_rincian']); ?></div>
                                        <div style="font-size:10px; color:#64748b;">(<?php echo $row['count_rincian']; ?> Baris Termin)</div>
                                        <?php if ($row['is_selisih_induk_vs_rincian']) { ?>
                                            <span class="badge badge-warning" title="Terbayar Induk != Total Rincian"><i class="fa fa-exclamation-triangle"></i> Induk Beda</span>
                                        <?php } ?>
                                    </td>
                                    <!-- Realisasi Faktur 749 -->
                                    <td class="text-right" style="background:#f0fdf4;">
                                        <div style="font-weight:700; color:#059669;">Rp <?php echo number_format($row['total_faktur_749_gross']); ?></div>
                                        <div style="font-size:10px; color:#64748b;">(<?php echo $row['count_faktur']; ?> Faktur Terbit)</div>
                                        <?php if ($row['is_selisih_rincian_vs_faktur']) { ?>
                                            <span class="badge badge-warning" title="Rincian Terbayar != Faktur Terbit"><i class="fa fa-exclamation"></i> Selisih Faktur</span>
                                        <?php } ?>
                                    </td>
                                    <!-- Status Selisih 3-Way Match -->
                                    <td class="text-center">
                                        <?php if ($row['is_matched']) { ?>
                                            <span class="badge badge-success"><i class="fa fa-check-circle"></i> MATCHED 100%</span>
                                        <?php } elseif ($row['is_anomaly']) { ?>
                                            <span class="badge badge-danger"><i class="fa fa-exclamation-triangle"></i> ANOMALI</span>
                                            <div style="margin-top:3px; display:flex; flex-direction:column; gap:2px; align-items:center;">
                                                <?php if ($row['is_selisih_project_vs_induk']) { ?>
                                                    <span class="badge badge-danger" style="font-size:8.5px;">Project vs Induk</span>
                                                <?php } ?>
                                                <?php if ($row['is_selisih_induk_vs_rincian']) { ?>
                                                    <span class="badge badge-warning" style="font-size:8.5px;">Induk vs Rincian</span>
                                                <?php } ?>
                                                <?php if ($row['is_selisih_rincian_vs_faktur']) { ?>
                                                    <span class="badge badge-warning" style="font-size:8.5px;">Rincian vs Faktur</span>
                                                <?php } ?>
                                                <?php if ($row['is_sisa_out_of_sync']) { ?>
                                                    <span class="badge badge-purple" style="font-size:8.5px;">Sisa Desinkron</span>
                                                <?php } ?>
                                            </div>
                                        <?php } else { ?>
                                            <span class="badge badge-secondary">NO TPS DATA</span>
                                        <?php } ?>
                                    </td>
                                    <!-- Rekomendasi Koreksi & Tombol Aksi -->
                                    <td>
                                        <div class="recom-box">
                                            <ul>
                                                <?php foreach ($row['rekomendasi'] as $recom) { ?>
                                                    <li><?php echo htmlspecialchars($recom); ?></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <!-- Action Buttons per Masalah -->
                                        <?php if (!empty($row['action_buttons'])) { ?>
                                            <div class="actions-wrapper">
                                                <?php foreach ($row['action_buttons'] as $act) {
                                                    $actJson = rawurlencode(json_encode($act));
                                                ?>
                                                    <button type="button" class="<?php echo $act['btn_class']; ?>" onclick="openFixModal('<?php echo $actJson; ?>')">
                                                        <i class="fa <?php echo $act['icon']; ?>"></i> <?php echo htmlspecialchars($act['label']); ?>
                                                    </button>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </td>
                                    <!-- Aksi / Toggle Detail -->
                                    <td class="text-center">
                                        <button type="button" class="btn-toggle" id="btn-toggle-<?php echo $row['project_id']; ?>" onclick="toggleDetail(<?php echo $row['project_id']; ?>)">
                                            <i class="fa fa-chevron-down"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                <!-- Accordion Detail Row -->
                                <tr id="detail-<?php echo $row['project_id']; ?>" style="display:none; background:#f8fafc;">
                                    <td colspan="10" style="padding:12px 18px;">
                                        <div style="font-weight:700; color:#0f172a; margin-bottom:8px;">
                                            <i class="fa fa-list-alt text-primary"></i> Rincian Audit Lengkap: Proyek #<?php echo $row['project_id']; ?> (<?php echo htmlspecialchars($row['project_nama']); ?>)
                                        </div>

                                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                                            <!-- Panel 1: Baris Induk 7499 & Rincian Termin -->
                                            <div>
                                                <div style="font-weight:600; color:#334155; margin-bottom:4px;"><i class="fa fa-database"></i> Payment Source Induk 7499:</div>
                                                <table class="sub-table">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Nomer</th>
                                                            <th>Jenis &rarr; Target</th>
                                                            <th class="text-right">Tagihan</th>
                                                            <th class="text-right">Terbayar</th>
                                                            <th class="text-right">Sisa</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($row['induk_details'])) { ?>
                                                            <tr><td colspan="6" class="text-center" style="color:#94a3b8;">Tidak ada baris Induk 7499</td></tr>
                                                        <?php } else { ?>
                                                            <?php foreach ($row['induk_details'] as $ind) { ?>
                                                                <tr>
                                                                    <td>#<?php echo $ind['id']; ?></td>
                                                                    <td><?php echo htmlspecialchars($ind['nomer']); ?></td>
                                                                    <td><code><?php echo $ind['jenis']; ?> &rarr; <?php echo $ind['target_jenis']; ?></code></td>
                                                                    <td class="text-right">Rp <?php echo number_format($ind['tagihan']); ?></td>
                                                                    <td class="text-right">Rp <?php echo number_format($ind['terbayar']); ?></td>
                                                                    <td class="text-right">Rp <?php echo number_format($ind['sisa']); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>

                                                <div style="font-weight:600; color:#334155; margin-top:10px; margin-bottom:4px;"><i class="fa fa-tags"></i> Rincian Termin (target_jenis = 749 / 7488):</div>
                                                <table class="sub-table">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Key / Tipe</th>
                                                            <th>Label & TOP</th>
                                                            <th class="text-right">Tagihan</th>
                                                            <th class="text-right">Terbayar</th>
                                                            <th class="text-right">Sisa (DB / Seharusnya)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($row['rincian_details'])) { ?>
                                                            <tr><td colspan="6" class="text-center" style="color:#94a3b8;">Tidak ada rincian termin</td></tr>
                                                        <?php } else { ?>
                                                            <?php foreach ($row['rincian_details'] as $rnc) {
                                                                $badgeClass = ($rnc['key'] === 'dp') ? 'badge-info' : (($rnc['key'] === 'retensi') ? 'badge-purple' : 'badge-warning');
                                                            ?>
                                                                <tr>
                                                                    <td>#<?php echo $rnc['id']; ?></td>
                                                                    <td>
                                                                        <span class="badge <?php echo $badgeClass; ?>">
                                                                            <?php echo strtoupper($rnc['key']); ?>
                                                                            <?php if ($rnc['is_legacy']) { echo " (Legacy)"; } ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars($rnc['label']); ?> (TOP #<?php echo $rnc['nomer_top']; ?>)</td>
                                                                    <td class="text-right">Rp <?php echo number_format($rnc['tagihan']); ?></td>
                                                                    <td class="text-right">Rp <?php echo number_format($rnc['terbayar']); ?></td>
                                                                    <td class="text-right">
                                                                        Rp <?php echo number_format($rnc['sisa']); ?>
                                                                        <?php if ($rnc['is_sisa_mismatch']) { ?>
                                                                            <div style="font-size:9px; color:#dc2626;">Seharusnya: Rp <?php echo number_format($rnc['sisa_seharusnya']); ?></div>
                                                                        <?php } ?>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Panel 2: Realisasi Faktur Riil 749/7488 & Validasi Formula -->
                                            <div>
                                                <div style="font-weight:600; color:#334155; margin-bottom:4px;"><i class="fa fa-file-text-o"></i> Realisasi Faktur 749 / 7488 Terbit:</div>
                                                <table class="sub-table">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Jenis & Nomer</th>
                                                            <th>Tgl Terbit</th>
                                                            <th class="text-right">Gross (Net)</th>
                                                            <th class="text-right">DPP</th>
                                                            <th class="text-right">PPN</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($row['faktur_details'])) { ?>
                                                            <tr><td colspan="6" class="text-center" style="color:#94a3b8;">Belum ada faktur 749/7488 terbit</td></tr>
                                                        <?php } else { ?>
                                                            <?php foreach ($row['faktur_details'] as $fdt) { ?>
                                                                <tr>
                                                                    <td>#<?php echo $fdt['id']; ?></td>
                                                                    <td><strong><?php echo htmlspecialchars($fdt['nomer']); ?></strong> (<?php echo $fdt['jenis']; ?>)</td>
                                                                    <td><?php echo htmlspecialchars($fdt['dtime']); ?></td>
                                                                    <td class="text-right">Rp <?php echo number_format($fdt['gross']); ?></td>
                                                                    <td class="text-right">Rp <?php echo number_format($fdt['dpp']); ?></td>
                                                                    <td class="text-right">Rp <?php echo number_format($fdt['ppn']); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>

                                                <div style="margin-top:10px; padding:10px; background:#f1f5f9; border-radius:6px; font-size:10.5px; border:1px solid #e2e8f0;">
                                                    <div style="font-weight:700; color:#334155; margin-bottom:4px;"><i class="fa fa-calculator"></i> Ringkasan Matriks 3-Way Match:</div>
                                                    <div>&bull; DPP Project vs Tagihan Induk: <strong>Rp <?php echo number_format($row['nilai_kontrak_dpp']); ?></strong> vs <strong>Rp <?php echo number_format($row['tagihan_induk']); ?></strong> (Selisih: Rp <?php echo number_format($row['selisih_project_vs_induk']); ?>)</div>
                                                    <div>&bull; Terbayar Induk vs Total Termin: <strong>Rp <?php echo number_format($row['terbayar_induk_db']); ?></strong> vs <strong>Rp <?php echo number_format($row['total_tagihan_rincian']); ?></strong> (Selisih: Rp <?php echo number_format($row['selisih_induk_vs_rincian']); ?>)</div>
                                                    <div>&bull; Total Faktur 749 Terbit: <strong>Rp <?php echo number_format($row['total_faktur_749_gross']); ?></strong> (PPN: Rp <?php echo number_format($row['total_faktur_749_ppn']); ?>)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Interactive Confirmation Modal with Query Preview -->
        <div id="fixModal" class="modal-backdrop">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="modal-title"><i class="fa fa-shield"></i> Konfirmasi Perbaikan Data</span>
                    <span style="cursor:pointer; font-size:18px;" onclick="closeFixModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div style="font-weight:700; color:#0f172a; margin-bottom:4px;" id="modal-desc">Deskripsi perbaikan...</div>
                    <div style="font-size:11px; color:#64748b; margin-bottom:10px;">
                        Berikut adalah daftar query database (SQL) yang akan dieksekusi untuk memperbaiki data:
                    </div>

                    <div class="sql-box" id="modal-sql">-- Query SQL preview</div>

                    <div style="font-size:10.5px; color:#475569; background:#f1f5f9; padding:8px 12px; border-radius:4px; border-left:3px solid #0284c7; margin-top:8px;">
                        <i class="fa fa-info-circle text-primary"></i> <strong>Keamanan Transaksi:</strong> Operasi ini dibungkus dalam Database Transaction (<code>$this->db->trans_start()</code>). Jika terjadi error pada salah satu query, seluruh perubahan akan di-rollback secara otomatis.
                    </div>

                    <div id="modal-result-box" style="display:none; margin-top:10px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeFixModal()">Batal</button>
                    <button type="button" class="btn-modal-exec" id="modal-exec-btn" onclick="executeFixAction()">
                        <i class="fa fa-play"></i> Konfirmasi & Eksekusi Perbaikan
                    </button>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
    }

    /**
     * Endpoint untuk mengeksekusi perbaikan query database secara aman (Database Transaction)
     */
    public function executeFixProjectTps3Way()
    {
        // Set response JSON
        header('Content-Type: application/json');

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'Hanya request POST yang diizinkan.'));
            return;
        }

        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput, true);

        if (empty($payload)) {
            $payload = $this->input->post();
        }

        $actionType = isset($payload['action_type']) ? trim((string)$payload['action_type']) : '';
        $projectId = isset($payload['project_id']) ? (int)$payload['project_id'] : 0;

        if ($projectId <= 0 || empty($actionType)) {
            echo json_encode(array('success' => false, 'message' => 'Parameter project_id atau action_type tidak valid.'));
            return;
        }

        $executedQueries = array();
        $this->db->trans_start();

        if ($actionType === 'fix_tagihan_induk') {
            $indukId = isset($payload['induk_id']) ? (int)$payload['induk_id'] : 0;
            $targetTagihan = isset($payload['target_tagihan']) ? (float)$payload['target_tagihan'] : 0;

            if ($indukId > 0 && $targetTagihan > 0) {
                // Update tagihan dan hitung ulang sisa
                $sql = "UPDATE `transaksi_payment_source` SET `tagihan` = ?, `sisa` = GREATEST(0, ? - `terbayar`) WHERE `id` = ? AND `project_id` = ?";
                $this->db->query($sql, array($targetTagihan, $targetTagihan, $indukId, $projectId));
                $executedQueries[] = $this->db->last_query();
            }
        } elseif ($actionType === 'sync_terbayar_induk') {
            $indukId = isset($payload['induk_id']) ? (int)$payload['induk_id'] : 0;
            $targetTerbayar = isset($payload['target_terbayar']) ? (float)$payload['target_terbayar'] : 0;

            if ($indukId > 0) {
                // Update terbayar dan hitung ulang sisa
                $sql = "UPDATE `transaksi_payment_source` SET `terbayar` = ?, `sisa` = GREATEST(0, `tagihan` - ?) WHERE `id` = ? AND `project_id` = ?";
                $this->db->query($sql, array($targetTerbayar, $targetTerbayar, $indukId, $projectId));
                $executedQueries[] = $this->db->last_query();
            }
        } elseif ($actionType === 'recalc_sisa_induk') {
            $indukId = isset($payload['induk_id']) ? (int)$payload['induk_id'] : 0;

            if ($indukId > 0) {
                $sql = "UPDATE `transaksi_payment_source` SET `sisa` = GREATEST(0, `tagihan` - `terbayar`) WHERE `id` = ? AND `project_id` = ?";
                $this->db->query($sql, array($indukId, $projectId));
                $executedQueries[] = $this->db->last_query();
            }
        } elseif ($actionType === 'recalc_sisa_rincian') {
            $rincianIds = isset($payload['rincian_ids']) && is_array($payload['rincian_ids']) ? $payload['rincian_ids'] : array();

            if (!empty($rincianIds)) {
                foreach ($rincianIds as $rId) {
                    $rId = (int)$rId;
                    if ($rId > 0) {
                        $sql = "UPDATE `transaksi_payment_source` SET `sisa` = GREATEST(0, `tagihan` - `terbayar`) WHERE `id` = ? AND `project_id` = ?";
                        $this->db->query($sql, array($rId, $projectId));
                        $executedQueries[] = $this->db->last_query();
                    }
                }
            }
        } elseif ($actionType === 'fix_all_project') {
            $queries = isset($payload['queries']) && is_array($payload['queries']) ? $payload['queries'] : array();

            if (!empty($queries)) {
                foreach ($queries as $q) {
                    $qTrim = trim((string)$q);
                    if (!empty($qTrim)) {
                        // Pastikan query hanya UPDATE transaksi_payment_source demi keamanan
                        if (stripos($qTrim, 'UPDATE') === 0 && stripos($qTrim, 'transaksi_payment_source') !== false) {
                            $this->db->query($qTrim);
                            $executedQueries[] = $this->db->last_query();
                        }
                    }
                }
            }
        } else {
            $this->db->trans_rollback();
            echo json_encode(array('success' => false, 'message' => 'Action type tidak dikenali.'));
            return;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Transaksi database gagal dieksekusi (Rollback otomatis).',
                'executed_queries' => $executedQueries
            ));
            return;
        }

        echo json_encode(array(
            'success' => true,
            'message' => 'Berhasil mengeksekusi ' . count($executedQueries) . ' query perbaikan database secara aman.',
            'executed_queries' => $executedQueries
        ));
    }

    /**
     * Tool Khusus Manajemen & Edit 4 Kolom Registry Project (items3, items4, items5, items7)
     * URL: base_url() . "ToolCek/editRegistryProject?project_id=..."
     */
    public function editRegistryProject()
    {
        $this->load->helper("url");
        $this->load->helper("he_url");
        $this->load->helper("he_misc");

        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : (isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0);
        $msgSuccess = "";
        $msgError = "";
        $executedQueries = array();
        $updatedTrxList = array();

        // 1. Handle POST Save
        if ($this->input->server('REQUEST_METHOD') === 'POST' && isset($_POST['action_save']) && $_POST['action_save'] == '1') {
            if ($projectId <= 0) {
                $msgError = "Project ID tidak valid.";
            } else {
                $rawItems3 = isset($_POST['items3_json']) ? trim($_POST['items3_json']) : '';
                $rawItems4 = isset($_POST['items4_json']) ? trim($_POST['items4_json']) : '';
                $rawItems5 = isset($_POST['items5_json']) ? trim($_POST['items5_json']) : '';
                $rawItems7 = isset($_POST['items7_json']) ? trim($_POST['items7_json']) : '';
                $syncPaymentSource = isset($_POST['sync_payment_source']) && $_POST['sync_payment_source'] == '1';

                $arrItems3 = !empty($rawItems3) ? json_decode($rawItems3, true) : array();
                $arrItems4 = !empty($rawItems4) ? json_decode($rawItems4, true) : array();
                $arrItems5 = !empty($rawItems5) ? json_decode($rawItems5, true) : array();
                $arrItems7 = !empty($rawItems7) ? json_decode($rawItems7, true) : array();

                if ($rawItems3 !== '' && $arrItems3 === null && json_last_error() !== JSON_ERROR_NONE) {
                    $msgError = "Format JSON ITEMS3 (Termin) tidak valid: " . json_last_error_msg();
                } elseif ($rawItems4 !== '' && $arrItems4 === null && json_last_error() !== JSON_ERROR_NONE) {
                    $msgError = "Format JSON ITEMS4 (DP) tidak valid: " . json_last_error_msg();
                } elseif ($rawItems5 !== '' && $arrItems5 === null && json_last_error() !== JSON_ERROR_NONE) {
                    $msgError = "Format JSON ITEMS5 (Retensi) tidak valid: " . json_last_error_msg();
                } elseif ($rawItems7 !== '' && $arrItems7 === null && json_last_error() !== JSON_ERROR_NONE) {
                    $msgError = "Format JSON ITEMS7 (Kontrak) tidak valid: " . json_last_error_msg();
                } else {
                    if (!is_array($arrItems3)) { $arrItems3 = array(); }
                    if (!is_array($arrItems4)) { $arrItems4 = array(); }
                    if (!is_array($arrItems5)) { $arrItems5 = array(); }
                    if (!is_array($arrItems7)) { $arrItems7 = array(); }

                    $this->db->trans_start();

                    // Ambil master project
                    $this->db->where('id', $projectId);
                    $projectRow = $this->db->get('project_produk')->row();

                    if (!$projectRow) {
                        $msgError = "Project dengan ID #$projectId tidak ditemukan.";
                        $this->db->trans_rollback();
                    } else {
                        // Kumpulkan hanya 3 ID transaksi utama project (588so, 588st, 588spo)
                        $targetTrxIds = array();

                        if (!empty($projectRow->quot_id) && $projectRow->quot_id > 0) {
                            $targetTrxIds[] = (int)$projectRow->quot_id;
                        }
                        if (!empty($projectRow->project_start_id) && $projectRow->project_start_id > 0) {
                            $targetTrxIds[] = (int)$projectRow->project_start_id;
                        }
                        if (!empty($projectRow->transaksi_id) && $projectRow->transaksi_id > 0) {
                            $targetTrxIds[] = (int)$projectRow->transaksi_id;
                        }

                        $targetTrxIds = array_values(array_unique(array_filter($targetTrxIds)));

                        if (empty($targetTrxIds)) {
                            $msgError = "Tidak ditemukan ID transaksi (588so, 588st, 588spo) untuk Project #$projectId.";
                            $this->db->trans_rollback();
                        } else {
                            $blobItems3 = blobEncode($arrItems3);
                            $blobItems4 = blobEncode($arrItems4);
                            $blobItems5 = blobEncode($arrItems5);
                            $blobItems7 = blobEncode($arrItems7);

                            // Update ke setiap transaksi_data_registry (hanya 3 ID)
                            foreach ($targetTrxIds as $tId) {
                                $this->db->select('transaksi_id');
                                $this->db->where('transaksi_id', $tId);
                                $regExist = $this->db->get('transaksi_data_registry')->row();

                                if ($regExist) {
                                    $this->db->where('transaksi_id', $tId);
                                    $this->db->update('transaksi_data_registry', array(
                                        'items3' => $blobItems3,
                                        'items4' => $blobItems4,
                                        'items5' => $blobItems5,
                                        'items7' => $blobItems7
                                    ));
                                    $executedQueries[] = $this->db->last_query();
                                    $updatedTrxList[] = "Transaksi ID #$tId (Registry Updated)";
                                } else {
                                    $this->db->insert('transaksi_data_registry', array(
                                        'transaksi_id' => $tId,
                                        'items3' => $blobItems3,
                                        'items4' => $blobItems4,
                                        'items5' => $blobItems5,
                                        'items7' => $blobItems7
                                    ));
                                    $executedQueries[] = $this->db->last_query();
                                    $updatedTrxList[] = "Transaksi ID #$tId (Registry Inserted)";
                                }
                            }

                            // Sync Payment Source jika opsi diaktifkan
                            if ($syncPaymentSource) {
                                $projHargaDpp = isset($projectRow->harga) ? (float)$projectRow->harga : 0;

                                // 1. Update / Pastikan Plafon Proyek (7499) bernilai Full Nilai Kontrak DPP
                                if ($projHargaDpp > 0) {
                                    $this->db->where('project_id', $projectId);
                                    $this->db->where('target_jenis', '7499');
                                    $tps7499 = $this->db->get('transaksi_payment_source')->row();

                                    // Hitung total DPP faktur yang sudah terbit (749) untuk update penyerapan terbayar & sisa
                                    $this->db->select('id, tagihan, ppn, dpp_ppn');
                                    $this->db->where('project_id', $projectId);
                                    $this->db->where('target_jenis', '749');
                                    $fakturRows = $this->db->get('transaksi_payment_source')->result();
                                    $totalFakturDpp = 0;
                                    if (!empty($fakturRows)) {
                                        foreach ($fakturRows as $fRow) {
                                            $fTag = (float)$fRow->tagihan;
                                            $fPpn = (float)$fRow->ppn;
                                            $fDpp = (float)$fRow->dpp_ppn;
                                            if ($fDpp > 0) {
                                                $totalFakturDpp += $fDpp;
                                            } else if ($fPpn > 0 && abs($fTag - $fDpp) < 10) {
                                                $totalFakturDpp += $fTag;
                                            } else {
                                                $totalFakturDpp += ($fTag / 1.11);
                                            }
                                        }
                                    }
                                    $sisaPlafonDpp = max(0, $projHargaDpp - $totalFakturDpp);

                                    if ($tps7499) {
                                        $this->db->where('id', $tps7499->id);
                                        $this->db->update('transaksi_payment_source', array(
                                            'tagihan' => $projHargaDpp,
                                            'terbayar' => $totalFakturDpp,
                                            'sisa' => $sisaPlafonDpp
                                        ));
                                        $executedQueries[] = $this->db->last_query();
                                    }
                                }

                                // 2. Hitung total Retensi dari items5
                                $sumRetensiIncl = 0;
                                foreach ($arrItems5 as $rRow) {
                                    $hrg = isset($rRow['harga']) ? (float)$rRow['harga'] : 0;
                                    $sumRetensiIncl += $hrg;
                                }
                                $sumRetensiDpp = $sumRetensiIncl > 0 ? ($sumRetensiIncl / 1.11) : 0;

                                // Update / Insert Payment Source Retensi (7488) - dalam satuan Gross / Incl PPN
                                if ($sumRetensiIncl > 0) {
                                    $this->db->where('project_id', $projectId);
                                    $this->db->where('target_jenis', '7488');
                                    $tpsRetensi = $this->db->get('transaksi_payment_source')->row();

                                    if ($tpsRetensi) {
                                        $this->db->where('id', $tpsRetensi->id);
                                        $this->db->update('transaksi_payment_source', array(
                                            'tagihan' => $sumRetensiIncl,
                                            'sisa' => $sumRetensiIncl
                                        ));
                                        $executedQueries[] = $this->db->last_query();
                                    } else {
                                        $refTrxId = (!empty($projectRow->quot_id) && $projectRow->quot_id > 0) ? $projectRow->quot_id : $projectRow->transaksi_id;
                                        $this->db->insert('transaksi_payment_source', array(
                                            'jenis' => '588so',
                                            'target_jenis' => '7488',
                                            'reference_jenis' => '588so',
                                            'transaksi_id' => $refTrxId,
                                            'project_id' => $projectRow->id,
                                            'project_nama' => $projectRow->nama,
                                            'extern_id' => $projectRow->customer_id,
                                            'extern_nama' => $projectRow->customer_nama,
                                            'cabang_id' => $projectRow->cabang_id,
                                            'cabang_nama' => $projectRow->cabang_nama,
                                            'label' => 'retensi',
                                            'tagihan' => $sumRetensiIncl,
                                            'sisa' => $sumRetensiIncl,
                                            'dtime' => date('Y-m-d H:i:s'),
                                            'fulldate' => date('Y-m-d'),
                                            'extern_nilai2' => $projectRow->harga
                                        ));
                                        $executedQueries[] = $this->db->last_query();
                                    }
                                }
                            }

                            $this->db->trans_complete();

                            if ($this->db->trans_status() === FALSE) {
                                $msgError = "Gagal menyimpan perubahan ke database (Transaction Rollback).";
                            } else {
                                $msgSuccess = "Berhasil mengupdate registry untuk " . count($targetTrxIds) . " transaksi (588so, 588st, 588spo) pada Project #$projectId!";
                            }
                        }
                    }
                }
            }
        }

        // 2. Fetch Data for Display (if projectId provided)
        $projectData = null;
        $decodedItems3 = array();
        $decodedItems4 = array();
        $decodedItems5 = array();
        $decodedItems7 = array();

        if ($projectId > 0) {
            $this->db->where('id', $projectId);
            $projectData = $this->db->get('project_produk')->row();

            if ($projectData) {
                // Cari transaksi referensi untuk membaca registry yang ada
                $readTrxIds = array();
                if (!empty($projectData->project_start_id) && $projectData->project_start_id > 0) {
                    $readTrxIds[] = (int)$projectData->project_start_id;
                }
                if (!empty($projectData->quot_id) && $projectData->quot_id > 0) {
                    $readTrxIds[] = (int)$projectData->quot_id;
                }
                if (!empty($projectData->transaksi_id) && $projectData->transaksi_id > 0) {
                    $readTrxIds[] = (int)$projectData->transaksi_id;
                }

                foreach ($readTrxIds as $rId) {
                    $this->db->select('transaksi_id, items3, items4, items5, items7');
                    $this->db->where('transaksi_id', $rId);
                    $regRow = $this->db->get('transaksi_data_registry')->row();

                    if ($regRow) {
                        if (empty($decodedItems3) && !empty($regRow->items3)) {
                            $decodedItems3 = blobDecode($regRow->items3);
                        }
                        if (empty($decodedItems4) && !empty($regRow->items4)) {
                            $decodedItems4 = blobDecode($regRow->items4);
                        }
                        if (empty($decodedItems5) && !empty($regRow->items5)) {
                            $decodedItems5 = blobDecode($regRow->items5);
                        }
                        if (empty($decodedItems7) && !empty($regRow->items7)) {
                            $decodedItems7 = blobDecode($regRow->items7);
                        }
                    }
                }

                // Ambil payment summary dari MdlTransaksi untuk status penagihan
                $this->load->model("MdlTransaksi");
                $paymentData = $this->MdlTransaksi->getProjectPaymentSummaryCI3($projectId);
                $arrTerminDpIssued = isset($paymentData["terminproject_dp"]) ? $paymentData["terminproject_dp"] : array();
                $arrTerminTerminIssued = isset($paymentData["terminproject_termin"]) ? $paymentData["terminproject_termin"] : (isset($paymentData["terminproject"]) ? $paymentData["terminproject"] : array());
                $arrTerminRetensiIssued = isset($paymentData["terminproject_retensi"]) ? $paymentData["terminproject_retensi"] : array();

                // Ambil record mentah dari tabel transaksi_payment_source
                $this->db->where('project_id', $projectId);
                $rawPaymentSources = $this->db->get('transaksi_payment_source')->result();

                // Hitung DPP alokasi dari array registry
                $ppnFactor = 11;
                $divisorPpn = 1 + ($ppnFactor / 100);
                $sumDpDpp = 0;
                $sumTerminDpp = 0;
                $sumRetensiDpp = 0;

                foreach ($decodedItems4 as $dpSpec) {
                    $sumDpDpp += isset($dpSpec['harga']) && (float)$dpSpec['harga'] > 0 ? ((float)$dpSpec['harga'] / $divisorPpn) : 0;
                }
                foreach ($decodedItems3 as $terminSpec) {
                    $sumTerminDpp += isset($terminSpec['harga']) && (float)$terminSpec['harga'] > 0 ? ((float)$terminSpec['harga'] / $divisorPpn) : 0;
                }
                foreach ($decodedItems5 as $retensiSpec) {
                    $sumRetensiDpp += isset($retensiSpec['harga']) && (float)$retensiSpec['harga'] > 0 ? ((float)$retensiSpec['harga'] / $divisorPpn) : 0;
                }

                $sumDpIncl = $sumDpDpp * $divisorPpn;
                $sumTerminIncl = $sumTerminDpp * $divisorPpn;
                $sumRetensiIncl = $sumRetensiDpp * $divisorPpn;

                $totalProjectDppSetting = $sumDpDpp + $sumTerminDpp + $sumRetensiDpp;
                if ($totalProjectDppSetting <= 0 && isset($projectData->harga)) {
                    $totalProjectDppSetting = (float)$projectData->harga;
                }

                // Helper closures untuk proses data penagihan
                $fnBuildReconSource = function($issuedRows, $ppnFac) {
                    $res = array(
                        'rowsHtml' => '',
                        'count' => 0,
                        'totalDpp' => 0,
                        'totalPpn' => 0,
                        'totalIncl' => 0,
                        'totalReturned' => 0,
                        'totalReturnedIncl' => 0,
                        'totalNetDpp' => 0,
                        'totalNetIncl' => 0,
                        'totalPaidIncl' => 0,
                        'totalOutstandingIncl' => 0
                    );
                    if (!is_array($issuedRows) || empty($issuedRows)) {
                        $res['rowsHtml'] = "<tr><td colspan='11' class='text-center text-muted' style='padding:12px;'>Belum ada data penerbitan.</td></tr>";
                        return $res;
                    }
                    $div = 1 + ($ppnFac / 100);
                    $rNo = 0;
                    foreach ($issuedRows as $iRow) {
                        $rNo++;
                        $dt = is_object($iRow) ? (isset($iRow->dtime) ? $iRow->dtime : '-') : (isset($iRow['dtime']) ? $iRow['dtime'] : '-');
                        $no = is_object($iRow) ? (isset($iRow->nomer) && !empty($iRow->nomer) ? $iRow->nomer : (isset($iRow->nomer_top) ? $iRow->nomer_top : '-')) : (isset($iRow['nomer']) && !empty($iRow['nomer']) ? $iRow['nomer'] : (isset($iRow['nomer_top']) ? $iRow['nomer_top'] : '-'));

                        // Nilai tagihan di transaksi_payment_source target 749 / 04467 / 7488
                        $rawTagihan = (float)(is_object($iRow) ? (isset($iRow->tagihan) ? $iRow->tagihan : 0) : (isset($iRow['tagihan']) ? $iRow['tagihan'] : 0));
                        $rowPpnField = (float)(is_object($iRow) ? (isset($iRow->ppn) ? $iRow->ppn : 0) : (isset($iRow['ppn']) ? $iRow['ppn'] : 0));
                        $rowDppField = (float)(is_object($iRow) ? (isset($iRow->dpp_ppn) ? $iRow->dpp_ppn : 0) : (isset($iRow['dpp_ppn']) ? $iRow['dpp_ppn'] : 0));

                        // Jika kolom ppn terisi dan tagihan dicatat sebagai DPP (tagihan == dpp_ppn), maka tagihan riil Incl PPN adalah tagihan + ppn
                        if ($rowPpnField > 0 && abs($rawTagihan - $rowDppField) < 10) {
                            $tagihanIncl = $rawTagihan + $rowPpnField;
                        } else {
                            $tagihanIncl = $rawTagihan;
                        }

                        $terbayarIncl = (float)(is_object($iRow) ? (isset($iRow->terbayar) ? $iRow->terbayar : 0) : (isset($iRow['terbayar']) ? $iRow['terbayar'] : 0));
                        $rawSisa = (float)(is_object($iRow) ? (isset($iRow->sisa) ? $iRow->sisa : 0) : (isset($iRow['sisa']) ? $iRow['sisa'] : 0));

                        if ($rowPpnField > 0 && abs($rawTagihan - $rowDppField) < 10 && $terbayarIncl <= 0) {
                            $sisaIncl = $tagihanIncl;
                        } else {
                            $sisaIncl = $rawSisa;
                        }
                        $retNomIncl = (float)(is_object($iRow) ? (isset($iRow->returned) ? $iRow->returned : 0) : (isset($iRow['returned']) ? $iRow['returned'] : 0));

                        $rowNetIncl = $tagihanIncl - $retNomIncl;
                        if ($rowNetIncl <= 0) { $rowNetIncl = $tagihanIncl; }
                        $rowNetDpp = $rowNetIncl / $div;
                        $rowPpn = $rowNetIncl - $rowNetDpp;

                        $rowRetNomDpp = $retNomIncl > 0 ? ($retNomIncl / $div) : 0;

                        $res['count']++;
                        $res['totalDpp'] += $rowNetDpp;
                        $res['totalPpn'] += $rowPpn;
                        $res['totalIncl'] += $tagihanIncl;
                        $res['totalReturned'] += $rowRetNomDpp;
                        $res['totalReturnedIncl'] += $retNomIncl;
                        $res['totalNetDpp'] += $rowNetDpp;
                        $res['totalNetIncl'] += $rowNetIncl;
                        $res['totalPaidIncl'] += $terbayarIncl;
                        $res['totalOutstandingIncl'] += $sisaIncl;

                        $statusBadge = ($sisaIncl <= 0 && $terbayarIncl > 0) ? '<span class="badge" style="background:#059669; color:#fff;">Lunas</span>' : ($terbayarIncl > 0 ? '<span class="badge" style="background:#0284c7; color:#fff;">Sebagian</span>' : '<span class="badge" style="background:#dc2626; color:#fff;">Belum Bayar</span>');

                        $res['rowsHtml'] .= "<tr>";
                        $res['rowsHtml'] .= "<td class='text-center'>{$rNo}</td>";
                        $res['rowsHtml'] .= "<td>" . htmlspecialchars(substr($dt, 0, 10)) . "</td>";
                        $res['rowsHtml'] .= "<td><strong>" . htmlspecialchars($no) . "</strong></td>";
                        $res['rowsHtml'] .= "<td class='text-right'>" . number_format($rowNetDpp) . "</td>";
                        $res['rowsHtml'] .= "<td class='text-right'>" . number_format($rowPpn) . "</td>";
                        $res['rowsHtml'] .= "<td class='text-right'>" . number_format($tagihanIncl) . "</td>";
                        $res['rowsHtml'] .= "<td class='text-right'>" . ($retNomIncl > 0 ? number_format($retNomIncl) : '-') . "</td>";
                        $res['rowsHtml'] .= "<td class='text-right'><strong>" . number_format($rowNetIncl) . "</strong></td>";
                        $res['rowsHtml'] .= "<td class='text-right'>" . number_format($terbayarIncl) . "</td>";
                        $res['rowsHtml'] .= "<td class='text-right' style='color:" . ($sisaIncl > 0 ? '#dc2626' : '#059669') . "; font-weight:bold;'>" . number_format($sisaIncl) . "</td>";
                        $res['rowsHtml'] .= "<td class='text-center'>{$statusBadge}</td>";
                        $res['rowsHtml'] .= "</tr>";
                    }
                    return $res;
                };

                $fnBuildCompSummary = function($alokasiDpp, $reconSource, $ppnFac) {
                    $alokasiDpp = (float)$alokasiDpp;
                    $div = 1 + ($ppnFac / 100);
                    $alokasiPpn = $alokasiDpp * ($ppnFac / 100);
                    $alokasiIncl = $alokasiDpp * $div;

                    $brutoDitagihIncl = isset($reconSource['totalIncl']) ? (float)$reconSource['totalIncl'] : 0;
                    $returIncl = isset($reconSource['totalReturnedIncl']) ? (float)$reconSource['totalReturnedIncl'] : 0;
                    $netoDitagihIncl = isset($reconSource['totalNetIncl']) ? (float)$reconSource['totalNetIncl'] : 0;
                    $netoDibayarIncl = isset($reconSource['totalPaidIncl']) ? (float)$reconSource['totalPaidIncl'] : 0;

                    $sisaNetoIncl = $alokasiIncl - $netoDitagihIncl;
                    if ($sisaNetoIncl < 0) { $sisaNetoIncl = 0; }

                    return array(
                        'alokasiDpp' => $alokasiDpp,
                        'alokasiPpn' => $alokasiPpn,
                        'alokasiIncl' => $alokasiIncl,
                        'brutoDitagihIncl' => $brutoDitagihIncl,
                        'returIncl' => $returIncl,
                        'netoDitagihIncl' => $netoDitagihIncl,
                        'netoDibayarIncl' => $netoDibayarIncl,
                        'sisaNetoIncl' => $sisaNetoIncl
                    );
                };

                $reconSourceDp = $fnBuildReconSource($arrTerminDpIssued, $ppnFactor);
                $reconSourceTermin = $fnBuildReconSource($arrTerminTerminIssued, $ppnFactor);
                $reconSourceRetensi = $fnBuildReconSource($arrTerminRetensiIssued, $ppnFactor);

                $dpSummary = $fnBuildCompSummary($sumDpDpp, $reconSourceDp, $ppnFactor);
                $terminSummary = $fnBuildCompSummary($sumTerminDpp, $reconSourceTermin, $ppnFactor);
                $retensiSummary = $fnBuildCompSummary($sumRetensiDpp, $reconSourceRetensi, $ppnFactor);

                $totalSisaNetoIncl = $dpSummary['sisaNetoIncl'] + $terminSummary['sisaNetoIncl'] + $retensiSummary['sisaNetoIncl'];

                $dpPct = $totalProjectDppSetting > 0 ? round(($sumDpDpp / $totalProjectDppSetting) * 100, 2) : 0;
                $terminAllocPct = $totalProjectDppSetting > 0 ? round(($sumTerminDpp / $totalProjectDppSetting) * 100, 2) : 0;
                $retensiPct = $totalProjectDppSetting > 0 ? round(($sumRetensiDpp / $totalProjectDppSetting) * 100, 2) : 0;
                $terminIssuedDpp = isset($reconSourceTermin['totalNetDpp']) ? (float)$reconSourceTermin['totalNetDpp'] : 0;
                $terminIssuedPct = $totalProjectDppSetting > 0 ? round(($terminIssuedDpp / $totalProjectDppSetting) * 100, 2) : 0;
                if ($terminIssuedPct > $terminAllocPct) { $terminIssuedPct = $terminAllocPct; }
                $remainingPct = $terminAllocPct - $terminIssuedPct;
                if ($remainingPct < 0) { $remainingPct = 0; }

                $projectHargaDpp = isset($projectData->harga) ? (float)$projectData->harga : 0;
                $projectHargaIncl = $projectHargaDpp * $divisorPpn;

                $realDpIncl = isset($reconSourceDp['totalNetIncl']) ? (float)$reconSourceDp['totalNetIncl'] : 0;
                $realDpDpp = $realDpIncl / $divisorPpn;

                $realTerminIncl = isset($reconSourceTermin['totalNetIncl']) ? (float)$reconSourceTermin['totalNetIncl'] : 0;
                $realTerminDpp = $realTerminIncl / $divisorPpn;

                $realRetensiIncl = isset($reconSourceRetensi['totalNetIncl']) ? (float)$reconSourceRetensi['totalNetIncl'] : 0;
                $realRetensiDpp = $realRetensiIncl / $divisorPpn;

                // --- DETEKSI KEANEHAN / INKONSISTENSI DATA REGISTRY & PAYMENT SOURCE ---
                $anomalyList = array();

                // 1. Deteksi Over-Billed pada Termin (Termin ditagih melebihi alokasi)
                if ($terminSummary['netoDitagihIncl'] > ($terminSummary['alokasiIncl'] + 1000)) {
                    $anomalyList[] = array(
                        "code" => "TERMIN_OVER_BILLED",
                        "title" => "Tagihan Termin Melebihi Alokasi Kontrak",
                        "desc" => "Tagihan/pembayaran Termin yang telah terbit secara nyata (<strong>Rp " . number_format($terminSummary['netoDitagihIncl']) . "</strong>) melebihi alokasi Termin di registry (<strong>Rp " . number_format($terminSummary['alokasiIncl']) . "</strong>)."
                    );
                }

                // 2. Deteksi Over-Billed pada DP (DP ditagih melebihi alokasi)
                if ($dpSummary['netoDitagihIncl'] > ($dpSummary['alokasiIncl'] + 1000)) {
                    $anomalyList[] = array(
                        "code" => "DP_OVER_BILLED",
                        "title" => "Tagihan DP Melebihi Alokasi Kontrak",
                        "desc" => "Tagihan/pembayaran DP yang telah terbit secara nyata (<strong>Rp " . number_format($dpSummary['netoDitagihIncl']) . "</strong>) melebihi alokasi DP di registry (<strong>Rp " . number_format($dpSummary['alokasiIncl']) . "</strong>)."
                    );
                }

                // 3. Deteksi Over-Billed pada Retensi (Retensi ditagih melebihi alokasi)
                if ($retensiSummary['netoDitagihIncl'] > ($retensiSummary['alokasiIncl'] + 1000)) {
                    $anomalyList[] = array(
                        "code" => "RETENSI_OVER_BILLED",
                        "title" => "Tagihan Retensi Melebihi Alokasi Kontrak",
                        "desc" => "Tagihan/pembayaran Retensi yang telah terbit secara nyata (<strong>Rp " . number_format($retensiSummary['netoDitagihIncl']) . "</strong>) melebihi alokasi Retensi di registry (<strong>Rp " . number_format($retensiSummary['alokasiIncl']) . "</strong>)."
                    );
                }

                // 4. Deteksi Total Alokasi Registry Tidak Pas dengan Nilai Kontrak Proyek
                $totalAllocDpp = $sumDpDpp + $sumTerminDpp + $sumRetensiDpp;
                $diffProject = abs($totalAllocDpp - $projectHargaDpp);
                if ($diffProject > 1000 && $projectHargaDpp > 0) {
                    $anomalyList[] = array(
                        "code" => "TOTAL_ALLOC_MISMATCH",
                        "title" => "Total Alokasi Registry Tidak Pas 100%",
                        "desc" => "Total akumulasi alokasi (DP + Termin + Retensi = <strong>Rp " . number_format($totalAllocDpp * $divisorPpn) . "</strong>) tidak sama dengan Nilai Kontrak Proyek (<strong>Rp " . number_format($projectHargaIncl) . "</strong>). Selisih: <strong>Rp " . number_format($diffProject * $divisorPpn) . "</strong>."
                    );
                }

                // 5. Deteksi Baris 'RETENSI' Nyasar di ITEMS3 (Termin)
                $hasRetensiInItems3 = false;
                if (!empty($decodedItems3)) {
                    foreach ($decodedItems3 as $rItm3) {
                        $nm = isset($rItm3['nama']) ? strtoupper((string)$rItm3['nama']) : '';
                        if (strpos($nm, 'RETENSI') !== false) {
                            $hasRetensiInItems3 = true;
                            break;
                        }
                    }
                }
                if ($hasRetensiInItems3) {
                    $anomalyList[] = array(
                        "code" => "RETENSI_IN_ITEMS3",
                        "title" => "Baris Retensi Berada di Kolom ITEMS3 (Termin)",
                        "desc" => "Ditemukan baris bertuliskan 'RETENSI' tercatat di ITEMS3 (Termin). Seharusnya Retensi dipisahkan ke ITEMS5 (Garansi)."
                    );
                }

                // 6. Deteksi Registry Kosong Padahal Ada Nilai Kontrak
                if (empty($decodedItems3) && empty($decodedItems4) && empty($decodedItems5) && $projectHargaDpp > 0) {
                    $anomalyList[] = array(
                        "code" => "REGISTRY_EMPTY",
                        "title" => "Data Alokasi Registry Masih Kosong",
                        "desc" => "Proyek memiliki nilai kontrak <strong>Rp " . number_format($projectHargaIncl) . "</strong>, namun belum ada data alokasi Termin/DP di registry."
                    );
                }

                // 7. Deteksi Inkonsistensi pada transaksi_payment_source
                $tpsTerminRow = null;
                $tpsRetensiRow = null;
                if (!empty($rawPaymentSources)) {
                    foreach ($rawPaymentSources as $tpsItem) {
                        if ($tpsItem->target_jenis === '7499') {
                            $tpsTerminRow = $tpsItem;
                        } else if ($tpsItem->target_jenis === '7488' || (isset($tpsItem->_key) && $tpsItem->_key === 'retensi')) {
                            $tpsRetensiRow = $tpsItem;
                        }
                    }
                }

                if (!$tpsTerminRow && $projectHargaDpp > 0) {
                    $anomalyList[] = array(
                        "code" => "TPS_TERMIN_MISSING",
                        "title" => "Record Payment Source Plafon Kontrak (7499) Tidak Ditemukan",
                        "desc" => "Tabel <code>transaksi_payment_source</code> untuk Plafon Kontrak (Target 7499) belum terbentuk pada project ini."
                    );
                } else if ($tpsTerminRow && abs((float)$tpsTerminRow->tagihan - $projectHargaDpp) > 1000) {
                    $anomalyList[] = array(
                        "code" => "TPS_TERMIN_MISMATCH",
                        "title" => "Nominal Plafon Kontrak (7499) Tidak Sinkron dengan Nilai Master Proyek",
                        "desc" => "Nominal tagihan di <code>transaksi_payment_source</code> Plafon Kontrak (<strong>Rp " . number_format($tpsTerminRow->tagihan * $divisorPpn) . " Incl PPN / Rp " . number_format($tpsTerminRow->tagihan) . " DPP</strong>) tidak sama dengan nilai kontrak master proyek (<strong>Rp " . number_format($projectHargaDpp * $divisorPpn) . " Incl PPN / Rp " . number_format($projectHargaDpp) . " DPP</strong>). Selisih: Rp " . number_format(abs($tpsTerminRow->tagihan - $projectHargaDpp) * $divisorPpn) . "."
                    );
                }

                if ($tpsRetensiRow && $sumRetensiIncl <= 0) {
                    $anomalyList[] = array(
                        "code" => "TPS_RETENSI_ORPHAN",
                        "title" => "Payment Source Retensi Aktif Padahal Registry Retensi Kosong",
                        "desc" => "Terdapat baris <code>transaksi_payment_source</code> Retensi (Target 7488 sebesar <strong>Rp " . number_format($tpsRetensiRow->tagihan) . "</strong>), namun pada registry items5 tidak ada alokasi retensi."
                    );
                } else if ($tpsRetensiRow && abs((float)$tpsRetensiRow->tagihan - $sumRetensiIncl) > 1000) {
                    $anomalyList[] = array(
                        "code" => "TPS_RETENSI_MISMATCH",
                        "title" => "Nominal Payment Source Retensi Tidak Sinkron dengan Registry",
                        "desc" => "Nominal tagihan di <code>transaksi_payment_source</code> Retensi (<strong>Rp " . number_format($tpsRetensiRow->tagihan) . "</strong>) tidak sama dengan alokasi Retensi di registry (<strong>Rp " . number_format($sumRetensiIncl) . "</strong>)."
                    );
                }

                $hasAnomaly = !empty($anomalyList);

                // --- GENERATE REKOMENDASI AUTO-GENERATE (TERMIN DISESUAIKAN DENGAN YANG DIBAYAR, SISANYA DI DP) ---
                $recItems3 = array();
                $recItems4 = array();
                $recItems5 = !empty($decodedItems5) ? $decodedItems5 : array();
                $recItems7 = !empty($decodedItems7) ? $decodedItems7 : array();

                // 1. Alokasi Retensi (items5)
                if ($realRetensiIncl > 0) {
                    $retPctCalc = $projectHargaDpp > 0 ? round(($realRetensiDpp / $projectHargaDpp) * 100, 2) : 0;
                    $recItems5 = array(
                        array(
                            "harga_project" => (string)$projectHargaDpp,
                            "persen" => (string)$retPctCalc,
                            "harga" => (string)round($realRetensiIncl, 0),
                            "tgl_akhir_garansi" => date('Y-12-31'),
                            "keterangan_garansi" => "Retensi Project"
                        )
                    );
                }
                $recRetensiDpp = 0;
                foreach ($recItems5 as $rRet) {
                    $h = isset($rRet['harga']) ? (float)$rRet['harga'] : 0;
                    $recRetensiDpp += ($h > 0) ? ($h / $divisorPpn) : 0;
                }

                // 2. Alokasi Termin (items3) & Uang Muka (items4)
                if ($realTerminIncl > 0 && $realDpIncl <= 0) {
                    // Kasus Utama: Termin sudah terbit/dibayar, DP belum terbit
                    // -> Alokasi Termin dinaikkan persis sebesar yang sudah ditagih/dibayar
                    $issuedTerminRows = !empty($arrTerminTerminIssued) ? $arrTerminTerminIssued : array();
                    $tUrut = 0;
                    $sumRecTerminDpp = 0;

                    if (!empty($issuedTerminRows)) {
                        foreach ($issuedTerminRows as $itRow) {
                            $tUrut++;
                            $tagIncl = (float)(is_object($itRow) ? (isset($itRow->tagihan) ? $itRow->tagihan : 0) : (isset($itRow['tagihan']) ? $itRow['tagihan'] : 0));
                            $retNomIncl = (float)(is_object($itRow) ? (isset($itRow->returned) ? $itRow->returned : 0) : (isset($itRow['returned']) ? $itRow['returned'] : 0));
                            $netIncl = $tagIncl - $retNomIncl;
                            if ($netIncl <= 0) { $netIncl = $tagIncl; }
                            $netDpp = $netIncl / $divisorPpn;
                            $sumRecTerminDpp += $netDpp;
                            $pctCalc = $projectHargaDpp > 0 ? round(($netDpp / $projectHargaDpp) * 100, 2) : 0;

                            $recItems3[] = array(
                                "urut" => (string)$tUrut,
                                "harga_project" => (string)$projectHargaDpp,
                                "nama" => "TERMIN " . $tUrut,
                                "progress" => (string)$pctCalc,
                                "persen" => (string)$pctCalc,
                                "harga" => (string)round($netIncl, 0)
                            );
                        }
                    } else {
                        $pctCalc = $projectHargaDpp > 0 ? round(($realTerminDpp / $projectHargaDpp) * 100, 2) : 0;
                        $recItems3[] = array(
                            "urut" => "1",
                            "harga_project" => (string)$projectHargaDpp,
                            "nama" => "TERMIN 1",
                            "progress" => (string)$pctCalc,
                            "persen" => (string)$pctCalc,
                            "harga" => (string)round($realTerminIncl, 0)
                        );
                        $sumRecTerminDpp = $realTerminDpp;
                    }

                    // -> DP dikosongkan karena seluruh penagihan proyek lama dialihkan lewat Termin
                    $recItems4 = array();

                    // -> Penanganan Sisa Nilai Proyek:
                    $sisaProjectDpp = $projectHargaDpp - ($sumRecTerminDpp + $recRetensiDpp);
                    if ($sisaProjectDpp > 100) {
                        $sisaProjectIncl = $sisaProjectDpp * $divisorPpn;
                        $sisaProjectPct = $projectHargaDpp > 0 ? round(($sisaProjectDpp / $projectHargaDpp) * 100, 2) : 0;

                        if ($sisaProjectPct <= 15 && empty($recItems5)) {
                            // Sisa <= 15% dan belum ada Retensi -> Masuk ke Retensi (items5)
                            $recItems5 = array(
                                array(
                                    "harga_project" => (string)$projectHargaDpp,
                                    "persen" => (string)$sisaProjectPct,
                                    "harga" => (string)round($sisaProjectIncl, 0),
                                    "tgl_akhir_garansi" => date('Y-12-31'),
                                    "keterangan_garansi" => "Retensi / Garansi Project"
                                )
                            );
                        } else {
                            // Sisa > 15% atau sudah ada Retensi -> Masuk ke Termin Pelunasan (items3)
                            $tUrut++;
                            $recItems3[] = array(
                                "urut" => (string)$tUrut,
                                "harga_project" => (string)$projectHargaDpp,
                                "nama" => "PELUNASAN / SISA TERMIN",
                                "progress" => (string)$sisaProjectPct,
                                "persen" => (string)$sisaProjectPct,
                                "harga" => (string)round($sisaProjectIncl, 0)
                            );
                        }
                    }
                } else if ($realDpIncl > 0) {
                    // Kasus DP sudah ada yang terbit
                    $dpPctCalc = $projectHargaDpp > 0 ? round(($realDpDpp / $projectHargaDpp) * 100, 2) : 0;
                    $recItems4 = array(
                        array(
                            "harga_project" => (string)$projectHargaDpp,
                            "persen" => (string)$dpPctCalc,
                            "harga" => (string)round($realDpIncl, 0)
                        )
                    );

                    $issuedTerminRows = !empty($arrTerminTerminIssued) ? $arrTerminTerminIssued : array();
                    $tUrut = 0;
                    $sumRecTerminDpp = 0;

                    foreach ($issuedTerminRows as $itRow) {
                        $tUrut++;
                        $tagIncl = (float)(is_object($itRow) ? (isset($itRow->tagihan) ? $itRow->tagihan : 0) : (isset($itRow['tagihan']) ? $itRow['tagihan'] : 0));
                        $retNomIncl = (float)(is_object($itRow) ? (isset($itRow->returned) ? $itRow->returned : 0) : (isset($itRow['returned']) ? $itRow['returned'] : 0));
                        $netIncl = $tagIncl - $retNomIncl;
                        if ($netIncl <= 0) { $netIncl = $tagIncl; }
                        $netDpp = $netIncl / $divisorPpn;
                        $sumRecTerminDpp += $netDpp;
                        $pctCalc = $projectHargaDpp > 0 ? round(($netDpp / $projectHargaDpp) * 100, 2) : 0;

                        $recItems3[] = array(
                            "urut" => (string)$tUrut,
                            "harga_project" => (string)$projectHargaDpp,
                            "nama" => "TERMIN " . $tUrut,
                            "progress" => (string)$pctCalc,
                            "persen" => (string)$pctCalc,
                            "harga" => (string)round($netIncl, 0)
                        );
                    }

                    // Sisa nilai kontrak diletakkan di termin sisa / pelunasan
                    $sisaTerminDpp = $projectHargaDpp - ($realDpDpp + $sumRecTerminDpp + $recRetensiDpp);
                    if ($sisaTerminDpp > 100) {
                        $tUrut++;
                        $sisaTerminIncl = $sisaTerminDpp * $divisorPpn;
                        $sisaTerminPct = $projectHargaDpp > 0 ? round(($sisaTerminDpp / $projectHargaDpp) * 100, 2) : 0;
                        $recItems3[] = array(
                            "urut" => (string)$tUrut,
                            "harga_project" => (string)$projectHargaDpp,
                            "nama" => "PELUNASAN / SISA TERMIN",
                            "progress" => (string)$sisaTerminPct,
                            "persen" => (string)$sisaTerminPct,
                            "harga" => (string)round($sisaTerminIncl, 0)
                        );
                    }
                } else {
                    // Kasus belum ada tagihan terbit sama sekali, pertahankan struktur settingan yang ada
                    $recItems3 = !empty($decodedItems3) ? $decodedItems3 : array();
                    $recItems4 = !empty($decodedItems4) ? $decodedItems4 : array();
                }

                // 3. Hitung Simulasi Rekomendasi
                $simSumDpDpp = 0;
                foreach ($recItems4 as $rDp) {
                    $h = isset($rDp['harga']) ? (float)$rDp['harga'] : 0;
                    $simSumDpDpp += ($h > 0) ? ($h / $divisorPpn) : 0;
                }
                $simSumTerminDpp = 0;
                foreach ($recItems3 as $stRow) {
                    $h = isset($stRow['harga']) ? (float)$stRow['harga'] : 0;
                    $simSumTerminDpp += ($h > 0) ? ($h / $divisorPpn) : 0;
                }
                $simSumRetensiDpp = 0;
                foreach ($recItems5 as $rRet) {
                    $h = isset($rRet['harga']) ? (float)$rRet['harga'] : 0;
                    $simSumRetensiDpp += ($h > 0) ? ($h / $divisorPpn) : 0;
                }

                $simTotalProjectDpp = $simSumDpDpp + $simSumTerminDpp + $simSumRetensiDpp;
                if ($simTotalProjectDpp <= 0) { $simTotalProjectDpp = $projectHargaDpp; }

                $simDpSummary = $fnBuildCompSummary($simSumDpDpp, $reconSourceDp, $ppnFactor);
                $simTerminSummary = $fnBuildCompSummary($simSumTerminDpp, $reconSourceTermin, $ppnFactor);
                $simRetensiSummary = $fnBuildCompSummary($simSumRetensiDpp, $reconSourceRetensi, $ppnFactor);

                $simTotalSisaNetoIncl = $simDpSummary['sisaNetoIncl'] + $simTerminSummary['sisaNetoIncl'] + $simRetensiSummary['sisaNetoIncl'];

                $simDpPct = $simTotalProjectDpp > 0 ? round(($simSumDpDpp / $simTotalProjectDpp) * 100, 2) : 0;
                $simTerminAllocPct = $simTotalProjectDpp > 0 ? round(($simSumTerminDpp / $simTotalProjectDpp) * 100, 2) : 0;
                $simRetensiPct = $simTotalProjectDpp > 0 ? round(($simSumRetensiDpp / $simTotalProjectDpp) * 100, 2) : 0;

                $simTerminIssuedDpp = isset($reconSourceTermin['totalNetDpp']) ? (float)$reconSourceTermin['totalNetDpp'] : 0;
                $simTerminIssuedPct = $simTotalProjectDpp > 0 ? round(($simTerminIssuedDpp / $simTotalProjectDpp) * 100, 2) : 0;
                if ($simTerminIssuedPct > $simTerminAllocPct) { $simTerminIssuedPct = $simTerminAllocPct; }
                $simRemainingPct = $simTerminAllocPct - $simTerminIssuedPct;
                if ($simRemainingPct < 0) { $simRemainingPct = 0; }
            } else {
                if (empty($msgError)) {
                    $msgError = "Project dengan ID #$projectId tidak ditemukan dalam database.";
                }
            }
        }

        $terminProjectLink = base_url() . "penerimaanprojek/Transaksi/index/7499";

        // 3. Render HTML UI
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tool Edit Registry Project (588spo, 588so, 588st)</title>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
            <style>
                * { box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 20px; font-size: 13px; }
                .container-custom { max-width: 1400px; margin: 0 auto; }
                .header-panel { background: #0f172a; color: #fff; padding: 18px 24px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
                .header-title { font-size: 18px; font-weight: 700; margin: 0 0 6px 0; display: flex; align-items: center; gap: 10px; }
                .header-subtitle { font-size: 12px; color: #94a3b8; margin: 0; }
                .card { background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 18px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
                .card-title { font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; }
                .form-group { margin-bottom: 14px; }
                .form-label { display: block; font-weight: 600; margin-bottom: 5px; color: #334155; }
                .form-control-custom { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: inherit; }
                .form-control-custom:focus { outline: none; border-color: #0284c7; box-shadow: 0 0 0 2px rgba(2,132,199,0.15); }
                .btn-tool { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12.5px; cursor: pointer; border: none; transition: 0.15s ease-in-out; text-decoration: none; }
                .btn-tool-primary { background: #0284c7; color: #fff; }
                .btn-tool-primary:hover { background: #0369a1; color: #fff; }
                .btn-tool-success { background: #059669; color: #fff; }
                .btn-tool-success:hover { background: #047857; color: #fff; }
                .btn-tool-secondary { background: #64748b; color: #fff; }
                .btn-tool-secondary:hover { background: #475569; color: #fff; }
                .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
                .json-textarea { width: 100%; height: 320px; font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace; font-size: 11.5px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fafafa; line-height: 1.45; resize: vertical; }
                .json-textarea:focus { background: #fff; border-color: #0284c7; outline: none; }
                .json-textarea-rec { background: #f0fdf4; border-color: #86efac; }
                .json-textarea-rec:focus { background: #fff; border-color: #10b981; }
                .badge-custom { display: inline-block; padding: 2px 7px; font-size: 10.5px; font-weight: 700; border-radius: 4px; }
                .badge-blue { background: #e0f2fe; color: #0369a1; }
                .badge-green { background: #d1fae5; color: #047857; }
                .badge-amber { background: #fef3c7; color: #92400e; }
                .badge-purple { background: #ede9fe; color: #6d28d9; }
                .target-box { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; margin-top: 10px; }
                .target-item { background: #f1f5f9; padding: 10px 14px; border-radius: 6px; border: 1px solid #e2e8f0; }
                .target-item-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 3px; }
                .target-item-val { font-size: 13px; font-weight: 700; color: #0f172a; }
                .quick-btn { font-size: 11.5px; padding: 4px 10px; margin-bottom: 8px; border-radius: 4px; border: 1px solid #cbd5e1; background: #f8fafc; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
                .quick-btn:hover { background: #e2e8f0; }
                .quick-btn-move { background: #fef3c7; border-color: #fde68a; color: #92400e; font-weight: 600; }
                .quick-btn-move:hover { background: #fde68a; }
                .table-recon { width: 100%; border-collapse: collapse; font-size: 12.5px; }
                .table-recon th, .table-recon td { padding: 9px 12px; border: 1px solid #e2e8f0; }
                .table-recon th { background: #f8fafc; font-weight: 700; color: #334155; }
                .text-green-bold { color: #059669; font-weight: 700; }
                .text-red-bold { color: #dc2626; font-weight: 700; }
                .col-actions { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 6px; }
                .btn-mini { font-size: 11px; padding: 2px 7px; border-radius: 4px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; font-weight: 500; }
                .btn-mini:hover { background: #f1f5f9; border-color: #94a3b8; }
                .btn-mini-primary { background: #e0f2fe; border-color: #bae6fd; color: #0284c7; font-weight: 600; }
                .btn-mini-primary:hover { background: #bae6fd; color: #0369a1; }
                .btn-mini-success { background: #d1fae5; border-color: #a7f3d0; color: #047857; font-weight: 600; }
                .btn-mini-success:hover { background: #a7f3d0; color: #065f46; }
                .btn-mini-amber { background: #fef3c7; border-color: #fde68a; color: #b45309; font-weight: 600; }
                .btn-mini-amber:hover { background: #fde68a; color: #92400e; }
                .btn-mini-purple { background: #ede9fe; border-color: #ddd6fe; color: #6d28d9; font-weight: 600; }
                .btn-mini-purple:hover { background: #ddd6fe; color: #5b21b6; }
                .btn-mini-danger { background: #fee2e2; border-color: #fecaca; color: #b91c1c; }
                .btn-mini-danger:hover { background: #fecaca; color: #991b1b; }
            </style>
        </head>
        <body>
        <div class="container-custom">
            <div class="header-panel">
                <div class="header-title">
                    <i class="fa fa-sliders"></i> Tool Manajemen & Edit 4 Kolom Registry Project
                </div>
                <div class="header-subtitle">
                    Modifikasi array <code>items3 (Termin)</code>, <code>items4 (Uang Muka)</code>, <code>items5 (Retensi)</code>, dan <code>items7 (Kontrak)</code> secara transparan untuk 3 transaksi project (<code>588so</code>, <code>588st</code>, <code>588spo</code>).
                </div>
            </div>

            <?php if (!empty($msgSuccess)) { ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> <strong>SUKSES:</strong> <?php echo htmlspecialchars($msgSuccess); ?>
                    <?php if (!empty($updatedTrxList)) { ?>
                        <ul style="margin: 6px 0 0 0; padding-left: 20px; font-size: 11.5px;">
                            <?php foreach ($updatedTrxList as $utrx) { ?>
                                <li><?php echo htmlspecialchars($utrx); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if (!empty($msgError)) { ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> <strong>ERROR:</strong> <?php echo htmlspecialchars($msgError); ?>
                </div>
            <?php } ?>

            <!-- Form Pilih Project ID -->
            <div class="card">
                <form method="GET" action="<?php echo base_url(); ?>ToolCek/editRegistryProject" style="display: flex; gap: 10px; align-items: flex-end;">
                    <div style="flex: 1; max-width: 250px;">
                        <label class="form-label"><i class="fa fa-search"></i> Masukkan Project ID:</label>
                        <input type="number" name="project_id" class="form-control-custom" placeholder="Contoh: 34" value="<?php echo $projectId > 0 ? $projectId : ''; ?>" required autofocus>
                    </div>
                    <button type="submit" class="btn-tool btn-tool-primary">
                        <i class="fa fa-download"></i> Ambil Data Registry
                    </button>
                    <?php if ($projectId > 0) { ?>
                        <a href="<?php echo base_url(); ?>ToolCek/editRegistryProject" class="btn-tool btn-tool-secondary">
                            <i class="fa fa-refresh"></i> Reset
                        </a>
                    <?php } ?>
                </form>
            </div>

            <?php if ($projectData) { ?>
                <!-- Panel Informasi Project & 3 Target Transaksi -->
                <div class="card">
                    <div class="card-title">
                        <i class="fa fa-building-o text-primary"></i> Data Project #<?php echo $projectData->id; ?>: <?php echo htmlspecialchars($projectData->nama); ?>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 12px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <div><strong>Customer:</strong> <?php echo htmlspecialchars($projectData->customer_nama); ?></div>
                        <div><strong>Cabang:</strong> <?php echo htmlspecialchars($projectData->cabang_nama); ?></div>
                        <div><strong>Nilai Kontrak (DPP):</strong> Rp <?php echo number_format($projectData->harga); ?></div>
                        <div><strong>Nilai Kontrak (Incl 11%):</strong> Rp <?php echo number_format($projectData->harga * 1.11); ?></div>
                    </div>

                    <div style="margin-top: 14px;">
                        <div style="font-weight: 700; color: #334155; font-size: 12px;">
                            <i class="fa fa-bullseye text-danger"></i> 3 ID Transaksi Utama Project yang Akan Diperbarui:
                        </div>
                        <div class="target-box">
                            <div class="target-item">
                                <div class="target-item-title">1. Quotation SO (588so)</div>
                                <div class="target-item-val">
                                    <?php if (!empty($projectData->quot_id)) { ?>
                                        #<?php echo $projectData->quot_id; ?> <span style="font-size:11px; font-weight:normal; color:#64748b;">(<?php echo htmlspecialchars($projectData->quot_nomer); ?>)</span>
                                    <?php } else { ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="target-item">
                                <div class="target-item-title">2. Project Start (588st)</div>
                                <div class="target-item-val">
                                    <?php if (!empty($projectData->project_start_id)) { ?>
                                        #<?php echo $projectData->project_start_id; ?> <span style="font-size:11px; font-weight:normal; color:#64748b;">(<?php echo htmlspecialchars($projectData->transaksi_no); ?>)</span>
                                    <?php } else { ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="target-item">
                                <div class="target-item-title">3. SO Project (588spo)</div>
                                <div class="target-item-val">
                                    <?php if (!empty($projectData->transaksi_id)) { ?>
                                        #<?php echo $projectData->transaksi_id; ?>
                                    <?php } else { ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PIVOT SUMMARY & ANALISA TRANSAKSI_PAYMENT_SOURCE -->
                    <?php
                        $tpsPivot = array(
                            'pagu_termin' => array(
                                'title' => 'Pagu Plafon Kontrak (Master AR)',
                                'target_badge' => '<span class="badge badge-blue">7499 (Pagu Termin)</span>',
                                'count' => 0,
                                'count_active' => 0,
                                'tagihanDpp' => 0,
                                'terbayarDpp' => 0,
                                'sisaDpp' => 0,
                                'icon' => 'fa-bookmark',
                                'color' => '#0284c7',
                                'desc' => 'Plafon pagu kontrak termin yang didaftarkan oleh SO/Start Project (belum PPN / DPP).'
                            ),
                            'ar_invoices' => array(
                                'title' => 'Faktur Tagihan AR Konsumen (Invoice Terbit)',
                                'target_badge' => '<span class="badge badge-green">749 (Termin) / 7488 (Retensi)</span>',
                                'count' => 0,
                                'count_active' => 0,
                                'tagihanDpp' => 0,
                                'terbayarDpp' => 0,
                                'sisaDpp' => 0,
                                'icon' => 'fa-file-text-o',
                                'color' => '#059669',
                                'desc' => 'Faktur piutang riil yang diterbitkan ke konsumen (termasuk PPN).'
                            ),
                            'um_deposit' => array(
                                'title' => 'Uang Muka Lepas Konsumen (Kas/Bank)',
                                'target_badge' => '<span class="badge badge-amber">04467 (Uang Muka)</span>',
                                'count' => 0,
                                'count_active' => 0,
                                'tagihanDpp' => 0,
                                'terbayarDpp' => 0,
                                'sisaDpp' => 0,
                                'icon' => 'fa-money',
                                'color' => '#d97706',
                                'desc' => 'Setoran uang muka dari modul kasir tanpa relasi langsung ke project.'
                            ),
                            'ap_vendor' => array(
                                'title' => 'Hutang Vendor & Biaya Project (AP Supplier)',
                                'target_badge' => '<span class="badge badge-purple">483 (Hutang Dagang)</span>',
                                'count' => 0,
                                'count_active' => 0,
                                'tagihanDpp' => 0,
                                'terbayarDpp' => 0,
                                'sisaDpp' => 0,
                                'icon' => 'fa-truck',
                                'color' => '#7c3aed',
                                'desc' => 'Kewajiban hutang atas pembelian material/jasa untuk project ini.'
                            ),
                            'others' => array(
                                'title' => 'Record Lainnya',
                                'target_badge' => '<span class="badge" style="background:#64748b;color:#fff;">Lainnya</span>',
                                'count' => 0,
                                'count_active' => 0,
                                'tagihanDpp' => 0,
                                'terbayarDpp' => 0,
                                'sisaDpp' => 0,
                                'icon' => 'fa-tag',
                                'color' => '#64748b',
                                'desc' => 'Record payment source tipe lainnya.'
                            )
                        );

                        if (!empty($rawPaymentSources)) {
                            foreach ($rawPaymentSources as $rTps) {
                                $tJenis = (string)$rTps->target_jenis;
                                $tagDpp = (float)$rTps->tagihan;
                                $terDpp = (float)$rTps->terbayar;
                                $sisDpp = (float)$rTps->sisa;

                                $catKey = 'others';
                                if ($tJenis === '7499') {
                                    $catKey = 'pagu_termin';
                                } else if ($tJenis === '749' || $tJenis === '7488') {
                                    $catKey = 'ar_invoices';
                                } else if ($tJenis === '04467') {
                                    $catKey = 'um_deposit';
                                } else if ($tJenis === '483' || strpos(strtolower($rTps->label), 'hutang') !== false) {
                                    $catKey = 'ap_vendor';
                                }

                                $tpsPivot[$catKey]['count']++;
                                if ($tagDpp > 0 || $terDpp > 0 || $sisDpp > 0) {
                                    $tpsPivot[$catKey]['count_active']++;
                                }
                                $tpsPivot[$catKey]['tagihanDpp'] += $tagDpp;
                                $tpsPivot[$catKey]['terbayarDpp'] += $terDpp;
                                $tpsPivot[$catKey]['sisaDpp'] += $sisDpp;
                            }
                        }
                    ?>

                    <div style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px; flex-wrap:wrap; gap:8px;">
                            <div style="font-weight: 700; color: #1e293b; font-size: 13px;">
                                <i class="fa fa-pie-chart text-primary"></i> Ringkasan Pivot <code>transaksi_payment_source</code> (Berdasarkan Kategori):
                            </div>
                            <span class="badge badge-blue" style="font-size:11px;">Total <?php echo count($rawPaymentSources); ?> Record Database</span>
                        </div>

                        <?php if (empty($rawPaymentSources)) { ?>
                            <div class="alert alert-warning" style="margin-bottom:0; font-size:11.5px; padding:8px 12px;">
                                <i class="fa fa-exclamation-triangle"></i> Belum ada record di tabel <code>transaksi_payment_source</code> untuk <code>project_id = <?php echo $projectId; ?></code>.
                            </div>
                        <?php } else { ?>
                            <!-- TABEL PIVOT SUMMARY -->
                            <table class="table-recon" style="font-size:12px; margin-bottom:12px;">
                                <thead>
                                    <tr style="background:#f1f5f9;">
                                        <th>Kategori Record Payment Source</th>
                                        <th style="width:170px;" class="text-center">Target Jenis</th>
                                        <th style="width:70px;" class="text-center">Record</th>
                                        <th class="text-right">Total Tagihan (DPP)</th>
                                        <th class="text-right">Total Tagihan (Incl 11%)</th>
                                        <th class="text-right">Total Terbayar (DPP)</th>
                                        <th class="text-right">Sisa / Saldo (DPP)</th>
                                        <th style="width:150px;" class="text-center">Status / Analisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($tpsPivot as $pKey => $pRow) {
                                        if ($pRow['count'] == 0) continue;
                                        $tagIncl = $pRow['tagihanDpp'] * $divisorPpn;
                                    ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight:700; color:<?php echo $pRow['color']; ?>;">
                                                    <i class="fa <?php echo $pRow['icon']; ?>"></i> <?php echo $pRow['title']; ?>
                                                </div>
                                                <div style="font-size:10.5px; color:#64748b; margin-top:2px;">
                                                    <?php echo $pRow['desc']; ?>
                                                </div>
                                            </td>
                                            <td class="text-center"><?php echo $pRow['target_badge']; ?></td>
                                            <td class="text-center">
                                                <strong><?php echo $pRow['count']; ?></strong>
                                                <?php if ($pRow['count_active'] < $pRow['count']) { ?>
                                                    <div style="font-size:9.5px; color:#94a3b8;">(<?php echo $pRow['count_active']; ?> aktif)</div>
                                                <?php } ?>
                                            </td>
                                            <td class="text-right font-weight-bold">Rp <?php echo number_format($pRow['tagihanDpp']); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($tagIncl); ?></td>
                                            <td class="text-right" style="color:#059669; font-weight:bold;">Rp <?php echo number_format($pRow['terbayarDpp']); ?></td>
                                            <td class="text-right" style="color:<?php echo ($pRow['sisaDpp'] > 0) ? '#dc2626' : '#059669'; ?>; font-weight:bold;">
                                                Rp <?php echo number_format($pRow['sisaDpp']); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($pKey === 'pagu_termin') { ?>
                                                    <?php if ($pRow['sisaDpp'] > 0) { ?>
                                                        <span class="badge badge-amber" style="font-size:10px;">Sisa Plafon Rp <?php echo number_format($pRow['sisaDpp']); ?></span>
                                                    <?php } else { ?>
                                                        <span class="badge badge-green" style="font-size:10px;">Plafon Terserap Penuh</span>
                                                    <?php } ?>
                                                <?php } else if ($pKey === 'ar_invoices') { ?>
                                                    <?php if ($pRow['sisaDpp'] > 0) { ?>
                                                        <span class="badge badge-amber" style="background:#fee2e2; color:#dc2626; font-size:10px;">Piutang Rp <?php echo number_format($pRow['sisaDpp']); ?></span>
                                                    <?php } else { ?>
                                                        <span class="badge badge-green" style="font-size:10px;"><i class="fa fa-check"></i> Piutang Lunas</span>
                                                    <?php } ?>
                                                <?php } else if ($pKey === 'ap_vendor') { ?>
                                                    <?php if ($pRow['sisaDpp'] > 0) { ?>
                                                        <span class="badge badge-amber" style="font-size:10px;">Hutang Rp <?php echo number_format($pRow['sisaDpp']); ?></span>
                                                    <?php } else { ?>
                                                        <span class="badge badge-green" style="font-size:10px;"><i class="fa fa-check"></i> Hutang Lunas</span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="badge badge-blue" style="font-size:10px;">Ok</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <!-- FILTER & COLLAPSIBLE DETAIL MENTAH DATABASE -->
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; margin-top:8px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                    <div style="font-size:11.5px; font-weight:700; color:#475569;">
                                        <i class="fa fa-table text-muted"></i> Filter Tampilan Rincian Record Database:
                                    </div>
                                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                        <button type="button" class="btn-tool tps-filter-btn" id="btn_filter_hide_zero" onclick="filterTpsTable('hide_zero')" style="font-size:11px; padding:3px 8px; background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">
                                            <i class="fa fa-filter"></i> Sembunyikan Baris Rp 0 (Hanya Baris Aktif)
                                        </button>
                                        <button type="button" class="btn-tool tps-filter-btn" id="btn_filter_ar" onclick="filterTpsTable('ar')" style="font-size:11px; padding:3px 8px;">
                                            <i class="fa fa-user"></i> Hanya Piutang Konsumen (749/7499)
                                        </button>
                                        <button type="button" class="btn-tool tps-filter-btn" id="btn_filter_ap" onclick="filterTpsTable('ap')" style="font-size:11px; padding:3px 8px;">
                                            <i class="fa fa-truck"></i> Hanya Hutang Vendor (483)
                                        </button>
                                        <button type="button" class="btn-tool tps-filter-btn" id="btn_filter_all" onclick="filterTpsTable('all')" style="font-size:11px; padding:3px 8px;">
                                            <i class="fa fa-list"></i> Tampilkan Semua (<?php echo count($rawPaymentSources); ?>)
                                        </button>
                                        <button type="button" class="btn-tool" onclick="toggleTpsRaw()" style="font-size:11px; padding:3px 8px; background:#f1f5f9; border-color:#cbd5e1;">
                                            <i class="fa fa-eye" id="tps_toggle_icon"></i> <span id="tps_toggle_text">Tutup Tabel Detail</span>
                                        </button>
                                    </div>
                                </div>

                                <div id="tps_raw_wrapper" style="margin-top:10px; overflow-x:auto;">
                                    <table class="table-recon" style="font-size:11px; background:#fff;">
                                        <thead>
                                            <tr style="background:#f1f5f9;">
                                                <th style="width:50px;" class="text-center">ID</th>
                                                <th style="width:130px;" class="text-center">Target & Kategori</th>
                                                <th>Label</th>
                                                <th style="width:140px;" class="text-center">Target Jenis</th>
                                                <th>Label / Keterangan</th>
                                                <th class="text-right">Nominal (DPP)</th>
                                                <th class="text-right">Nominal (Incl 11%)</th>
                                                <th class="text-right">Terbayar (DPP)</th>
                                                <th class="text-right">Sisa (DPP)</th>
                                                <th style="width:120px;" class="text-center">Waktu Dibuat</th>
                                                <th style="width:100px;" class="text-center">Status Sync</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($rawPaymentSources as $tpsRow) {
                                                $tJenis = (string)$tpsRow->target_jenis;
                                                $tagDpp = (float)$tpsRow->tagihan;
                                                $terDpp = (float)$tpsRow->terbayar;
                                                $sisDpp = (float)$tpsRow->sisa;
                                                $isZero = ($tagDpp == 0 && $terDpp == 0 && $sisDpp == 0);

                                                $catKey = 'others';
                                                $rKey = isset($tpsRow->_key) ? strtolower(trim($tpsRow->_key)) : '';
                                                $rLabel = strtolower(trim($tpsRow->label));

                                                if ($tJenis === '7499') {
                                                    $catKey = 'pagu_termin';
                                                    $tgtDesc = '<span class="badge badge-blue">7499 (Pagu Termin)</span>';
                                                    $rowTagDpp = $tagDpp;
                                                    $rowTagIncl = $tagDpp * $divisorPpn;
                                                    $rowTerDpp = $terDpp;
                                                    $rowSisDpp = $sisDpp;
                                                } else if ($tJenis === '749') {
                                                    $catKey = 'ar_invoices';
                                                    if ($rKey === 'retensi' || strpos($rLabel, 'retensi') !== false) {
                                                        $tgtDesc = '<span class="badge badge-purple">749 (Retensi)</span>';
                                                    } else if ($rKey === 'dp' || strpos($rLabel, 'uang muka') !== false) {
                                                        $tgtDesc = '<span class="badge badge-amber">749 (DP)</span>';
                                                    } else {
                                                        $tgtDesc = '<span class="badge badge-green">749 (Termin)</span>';
                                                    }
                                                    $rowPpnVal = (float)(isset($tpsRow->ppn) ? $tpsRow->ppn : 0);
                                                    $rowDppVal = (float)(isset($tpsRow->dpp_ppn) ? $tpsRow->dpp_ppn : 0);
                                                    if ($rowPpnVal > 0 && abs($tagDpp - $rowDppVal) < 10) {
                                                        $rowTagIncl = $tagDpp + $rowPpnVal;
                                                        $rowTagDpp = $tagDpp;
                                                        $rowTerDpp = $terDpp;
                                                        $rowSisDpp = $sisDpp;
                                                    } else {
                                                        $rowTagDpp = $rowDppVal > 0 ? $rowDppVal : ($tagDpp / $divisorPpn);
                                                        $rowTagIncl = $tagDpp;
                                                        $rowTerDpp = $terDpp / $divisorPpn;
                                                        $rowSisDpp = $sisDpp / $divisorPpn;
                                                    }
                                                } else if ($tJenis === '7488') {
                                                    $catKey = 'ar_invoices';
                                                    $tgtDesc = '<span class="badge badge-purple">7488 (Retensi)</span>';
                                                    $rowTagDpp = $tagDpp / $divisorPpn;
                                                    $rowTagIncl = $tagDpp;
                                                    $rowTerDpp = $terDpp / $divisorPpn;
                                                    $rowSisDpp = $sisDpp / $divisorPpn;
                                                } else if ($tJenis === '04467') {
                                                    $catKey = 'um_deposit';
                                                    $tgtDesc = '<span class="badge badge-amber">04467 (Uang Muka)</span>';
                                                    $rowTagDpp = $tagDpp;
                                                    $rowTagIncl = $tagDpp * $divisorPpn;
                                                    $rowTerDpp = $terDpp;
                                                    $rowSisDpp = $sisDpp;
                                                } else if ($tJenis === '483' || strpos(strtolower($tpsRow->label), 'hutang') !== false) {
                                                    $catKey = 'ap_vendor';
                                                    $tgtDesc = '<span class="badge" style="background:#ede9fe; color:#6d28d9;">483 (Hutang AP)</span>';
                                                    $rowTagDpp = $tagDpp;
                                                    $rowTagIncl = $tagDpp * $divisorPpn;
                                                    $rowTerDpp = $terDpp;
                                                    $rowSisDpp = $sisDpp;
                                                } else {
                                                    $tgtDesc = '<span class="badge badge-amber">' . htmlspecialchars($tJenis) . '</span>';
                                                    $rowTagDpp = $tagDpp;
                                                    $rowTagIncl = $tagDpp * $divisorPpn;
                                                    $rowTerDpp = $terDpp;
                                                    $rowSisDpp = $sisDpp;
                                                }

                                                $isSync = true;
                                                $syncNote = "Sinkron";
                                                if ($tJenis === '7499') {
                                                    if (abs($tagDpp - $projectHargaDpp) > 1000) {
                                                        $isSync = false;
                                                        $syncNote = "Beda dg Master";
                                                    }
                                                } else if (($tJenis === '749' && $rKey === 'retensi') || $tJenis === '7488') {
                                                    if ($rowTagDpp > 0 && abs($rowTagDpp - $sumRetensiDpp) > 1000) {
                                                        $isSync = false;
                                                        $syncNote = "Beda dg Items5";
                                                    }
                                                }
                                            ?>
                                                <tr class="tps-raw-row" data-cat="<?php echo $catKey; ?>" data-is-zero="<?php echo $isZero ? '1' : '0'; ?>" style="<?php echo $isZero ? 'color:#94a3b8; background:#fcfcfc;' : ''; ?>">
                                                    <td class="text-center"><strong>#<?php echo $tpsRow->id; ?></strong></td>
                                                    <td class="text-center"><?php echo $tgtDesc; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($tpsRow->label); ?></strong>
                                                        <?php if ($isZero) { ?>
                                                            <span style="font-size:9.5px; color:#cbd5e1; margin-left:4px;">(Placeholder Rp 0)</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="text-right"><?php echo ($rowTagDpp > 0) ? 'Rp ' . number_format($rowTagDpp) : '-'; ?></td>
                                                    <td class="text-right"><?php echo ($rowTagIncl > 0) ? 'Rp ' . number_format($rowTagIncl) : '-'; ?></td>
                                                    <td class="text-right" style="color:<?php echo ($rowTerDpp > 0) ? '#059669' : '#94a3b8'; ?>;"><?php echo ($rowTerDpp > 0) ? 'Rp ' . number_format($rowTerDpp) : '-'; ?></td>
                                                    <td class="text-right" style="color:<?php echo ($rowSisDpp > 0) ? '#dc2626' : ($isZero ? '#94a3b8' : '#059669'); ?>; font-weight:<?php echo ($rowSisDpp > 0) ? 'bold' : 'normal'; ?>;">
                                                        <?php echo ($rowSisDpp > 0) ? 'Rp ' . number_format($rowSisDpp) : ($isZero ? '-' : 'Rp 0'); ?>
                                                    </td>
                                                    <td class="text-center text-muted" style="font-size:10px;"><?php echo htmlspecialchars(substr($tpsRow->dtime, 0, 16)); ?></td>
                                                    <td class="text-center">
                                                        <?php if ($isZero) { ?>
                                                            <span class="badge" style="background:#e2e8f0; color:#64748b; font-size:9.5px;">Auto Dummy</span>
                                                        <?php } else if ($isSync) { ?>
                                                            <span class="badge" style="background:#059669; color:#fff; font-size:9.5px;"><i class="fa fa-check"></i> <?php echo $syncNote; ?></span>
                                                        <?php } else { ?>
                                                            <span class="badge" style="background:#dc2626; color:#fff; font-size:9.5px;"><i class="fa fa-exclamation-circle"></i> <?php echo $syncNote; ?></span>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <script>
                                function filterTpsTable(mode) {
                                    var rows = document.querySelectorAll('.tps-raw-row');
                                    rows.forEach(function(r) {
                                        var isZero = r.getAttribute('data-is-zero') === '1';
                                        var cat = r.getAttribute('data-cat');
                                        if (mode === 'all') {
                                            r.style.display = '';
                                        } else if (mode === 'hide_zero') {
                                            r.style.display = isZero ? 'none' : '';
                                        } else if (mode === 'ar') {
                                            r.style.display = (cat === 'pagu_termin' || cat === 'ar_invoices') ? '' : 'none';
                                        } else if (mode === 'ap') {
                                            r.style.display = (cat === 'ap_vendor') ? '' : 'none';
                                        }
                                    });
                                    document.querySelectorAll('.tps-filter-btn').forEach(function(b) {
                                        b.style.background = '#f8fafc';
                                        b.style.color = '#334155';
                                        b.style.borderColor = '#cbd5e1';
                                    });
                                    var actBtn = document.getElementById('btn_filter_' + mode);
                                    if (actBtn) {
                                        actBtn.style.background = '#e0f2fe';
                                        actBtn.style.color = '#0369a1';
                                        actBtn.style.borderColor = '#bae6fd';
                                    }
                                }
                                function toggleTpsRaw() {
                                    var w = document.getElementById('tps_raw_wrapper');
                                    var icon = document.getElementById('tps_toggle_icon');
                                    var txt = document.getElementById('tps_toggle_text');
                                    if (w.style.display === 'none') {
                                        w.style.display = 'block';
                                        icon.className = 'fa fa-eye';
                                        txt.innerText = 'Tutup Tabel Detail';
                                    } else {
                                        w.style.display = 'none';
                                        icon.className = 'fa fa-eye-slash';
                                        txt.innerText = 'Buka Tabel Detail';
                                    }
                                }
                                // Default filter: Sembunyikan baris dummy Rp 0
                                document.addEventListener('DOMContentLoaded', function() {
                                    filterTpsTable('hide_zero');
                                });
                            </script>
                        <?php } ?>
                    </div>
                </div>

                <!-- PANEL STATUS PENAGIHAN PROYEK (SAAT INI) -->
                <div class="card" style="border-radius:8px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
                    <div style="font-size: 15px; font-weight: 700; color: #dc2626; margin-bottom: 8px;">
                        STATUS PENAGIHAN PROYEK (<?php echo strtoupper(htmlspecialchars($projectData->nama)); ?> <?php echo !empty($projectData->quot_nomer) ? '. ' . htmlspecialchars($projectData->quot_nomer) : ''; ?>)
                    </div>
                    <div style="font-size: 12px; color: #475569; margin-bottom: 10px;">
                        Visualisasi Alokasi vs Penagihan (Total: <?php echo number_format($totalProjectDppSetting); ?>):
                    </div>

                    <!-- Visual Progress Bar -->
                    <div style="display: flex; height: 28px; border-radius: 6px; overflow: hidden; margin-bottom: 16px; background: #e2e8f0;">
                        <?php if ($dpPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#fff; font-size:11.5px; font-weight:bold; background:#5bc0de; width:<?php echo number_format($dpPct, 2); ?>%;">
                                DP <?php echo number_format($dpPct, 2); ?>%
                            </div>
                        <?php } ?>
                        <?php if ($terminIssuedPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#fff; font-size:11.5px; font-weight:bold; background:#5cb85c; width:<?php echo number_format($terminIssuedPct, 2); ?>%;">
                                Termin Terbit <?php echo number_format($terminIssuedPct, 2); ?>%
                            </div>
                        <?php } ?>
                        <?php if ($remainingPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#64748b; font-size:11.5px; font-weight:bold; background:#e2e8f0; width:<?php echo number_format($remainingPct, 2); ?>%;">
                                Sisa Termin <?php echo number_format($remainingPct, 2); ?>%
                            </div>
                        <?php } ?>
                        <?php if ($retensiPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#fff; font-size:11.5px; font-weight:bold; background:#f0ad4e; width:<?php echo number_format($retensiPct, 2); ?>%;">
                                Retensi <?php echo number_format($retensiPct, 2); ?>%
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Table Status Penagihan -->
                    <div class="table-responsive" style="margin-bottom: 0;">
                        <table class="table-recon table table-bordered">
                            <thead>
                                <tr style="background: #f8fafc;">
                                    <th>Komponen Biaya</th>
                                    <th class="text-right">Alokasi (%)</th>
                                    <th class="text-right">Alokasi DPP</th>
                                    <th class="text-right">Alokasi PPN</th>
                                    <th class="text-center">Alokasi<br>(Incl.PPN)</th>
                                    <th class="text-center">Bruto Ditagih<br>(Incl.PPN)</th>
                                    <th class="text-center">Retur/Pembatalan<br>(Incl.PPN)</th>
                                    <th class="text-center">Neto Ditagih<br>(Incl.PPN)</th>
                                    <th class="text-center">Neto Dibayar<br>(Incl.PPN)</th>
                                    <th class="text-center">Sisa Neto<br>(Incl.PPN)</th>
                                    <th class="text-center" width="180">Aksi Utama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row Uang Muka (DP) -->
                                <tr>
                                    <td><strong>Uang Muka (DP)</strong></td>
                                    <td class="text-right"><?php echo number_format($dpPct, 2); ?>%</td>
                                    <td class="text-right"><?php echo number_format($dpSummary['alokasiDpp']); ?></td>
                                    <td class="text-right"><?php echo number_format($dpSummary['alokasiPpn']); ?></td>
                                    <td class="text-right"><?php echo number_format($dpSummary['alokasiIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($dpSummary['brutoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($dpSummary['returIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($dpSummary['netoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($dpSummary['netoDibayarIncl']); ?></td>
                                    <td class="text-right <?php echo $dpSummary['sisaNetoIncl'] > 0 ? 'text-red-bold' : 'text-green-bold'; ?>">
                                        <?php echo number_format($dpSummary['sisaNetoIncl']); ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-primary" onclick="window.open('<?php echo $terminProjectLink; ?>', '_blank');">Terbitkan DP</button>
                                        <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal_recon_src_dp">Lihat Sumber</button>
                                    </td>
                                </tr>

                                <!-- Row Termin Progres -->
                                <tr>
                                    <td><strong>Termin Progres</strong></td>
                                    <td class="text-right"><?php echo number_format($terminAllocPct, 2); ?>%</td>
                                    <td class="text-right"><?php echo number_format($terminSummary['alokasiDpp']); ?></td>
                                    <td class="text-right"><?php echo number_format($terminSummary['alokasiPpn']); ?></td>
                                    <td class="text-right"><?php echo number_format($terminSummary['alokasiIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($terminSummary['brutoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($terminSummary['returIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($terminSummary['netoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($terminSummary['netoDibayarIncl']); ?></td>
                                    <td class="text-right <?php echo $terminSummary['sisaNetoIncl'] > 0 ? 'text-red-bold' : 'text-green-bold'; ?>">
                                        <?php echo number_format($terminSummary['sisaNetoIncl']); ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-info" onclick="window.open('<?php echo $terminProjectLink; ?>', '_blank');">Terbitkan Termin</button>
                                        <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal_recon_src_termin">Lihat Sumber</button>
                                    </td>
                                </tr>

                                <!-- Row Retensi -->
                                <tr>
                                    <td><strong>Retensi</strong></td>
                                    <td class="text-right"><?php echo number_format($retensiPct, 2); ?>%</td>
                                    <td class="text-right"><?php echo number_format($retensiSummary['alokasiDpp']); ?></td>
                                    <td class="text-right"><?php echo number_format($retensiSummary['alokasiPpn']); ?></td>
                                    <td class="text-right"><?php echo number_format($retensiSummary['alokasiIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($retensiSummary['brutoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($retensiSummary['returIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($retensiSummary['netoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($retensiSummary['netoDibayarIncl']); ?></td>
                                    <td class="text-right <?php echo $retensiSummary['sisaNetoIncl'] > 0 ? 'text-red-bold' : 'text-green-bold'; ?>">
                                        <?php echo number_format($retensiSummary['sisaNetoIncl']); ?>
                                    </td>
                                    <td class="text-center">
                                        <small style="color:#64748b;">(Menunggu Jatuh Tempo)</small><br>
                                        <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal_recon_src_retensi" style="margin-top:2px;">Lihat Sumber</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f1f5f9; font-weight: bold;">
                                    <td colspan="9" class="text-right">Total Sisa Saldo Di Modul Termin (Incl.PPN):</td>
                                    <td colspan="2" style="font-size: 13.5px; color: <?php echo $totalSisaNetoIncl > 0 ? '#dc2626' : '#059669'; ?>;">Rp <?php echo number_format($totalSisaNetoIncl); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if ($hasAnomaly) { ?>
                        <div class="alert alert-warning" style="margin-top: 14px; margin-bottom: 0; padding: 12px 16px; font-size: 12px; border-radius: 6px; background:#fffbeb; border:1px solid #fef3c7; color:#92400e;">
                            <div style="font-size: 13px; font-weight: 700; margin-bottom: 6px;">
                                <i class="fa fa-exclamation-triangle text-warning"></i> Terdeteksi <?php echo count($anomalyList); ?> Keanehan / Ketidaksesuaian Data Pada Proyek Ini:
                            </div>
                            <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                                <?php foreach ($anomalyList as $anm) { ?>
                                    <li><strong><?php echo $anm['title']; ?>:</strong> <?php echo $anm['desc']; ?></li>
                                <?php } ?>
                            </ul>
                            <div style="margin-top: 8px; font-size: 11.5px; color: #78350f;">
                                👉 Silakan lihat <strong>Panel Rekomendasi Auto-Generate</strong> di bagian bawah untuk menormalkan data ini secara instan.
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-success" style="margin-top: 14px; margin-bottom: 0; padding: 10px 14px; font-size: 12px; border-radius: 6px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;">
                            <i class="fa fa-check-circle text-success"></i> <strong>Data Registry Normal:</strong> Alokasi proyek telah sesuai dan seimbang dengan nilai kontrak. Tidak ditemukan keanehan data sehingga tidak diperlukan rekomendasi perbaikan.
                        </div>
                    <?php } ?>
                </div>

                <!-- Modals Sumber Penagihan (DP, Termin, Retensi) -->
                <div class="modal fade" id="modal_recon_src_dp" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"><i class="fa fa-money text-primary"></i> Sumber Data Penagihan - DP</h4>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped no-margin">
                                        <thead>
                                            <tr style="background:#f8fafc;">
                                                <th width="40" class="text-center">No</th>
                                                <th>Tgl</th>
                                                <th>Nomer</th>
                                                <th class="text-right">DPP</th>
                                                <th class="text-right">PPN</th>
                                                <th class="text-center">Bruto<br>(Incl.PPN)</th>
                                                <th class="text-center">Retur/Pembatalan<br>(Incl.PPN)</th>
                                                <th class="text-center">Neto Ditagih<br>(Incl.PPN)</th>
                                                <th class="text-center">Neto Dibayar<br>(Incl.PPN)</th>
                                                <th class="text-center">Sisa Neto<br>(Incl.PPN)</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody><?php echo $reconSourceDp['rowsHtml']; ?></tbody>
                                        <tfoot>
                                            <tr style="background:#f1f5f9; font-weight:bold;">
                                                <td colspan="3" class="text-right">Total</td>
                                                <td class="text-right"><?php echo number_format($reconSourceDp['totalDpp']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceDp['totalPpn']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceDp['totalIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceDp['totalReturnedIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceDp['totalNetIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceDp['totalPaidIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceDp['totalOutstandingIncl']); ?></td>
                                                <td class="text-center"><?php echo number_format($reconSourceDp['count']); ?> row</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modal_recon_src_termin" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"><i class="fa fa-table text-success"></i> Sumber Data Penagihan - Termin</h4>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped no-margin">
                                        <thead>
                                            <tr style="background:#f8fafc;">
                                                <th width="40" class="text-center">No</th>
                                                <th>Tgl</th>
                                                <th>Nomer</th>
                                                <th class="text-right">DPP</th>
                                                <th class="text-right">PPN</th>
                                                <th class="text-center">Bruto<br>(Incl.PPN)</th>
                                                <th class="text-center">Retur/Pembatalan<br>(Incl.PPN)</th>
                                                <th class="text-center">Neto Ditagih<br>(Incl.PPN)</th>
                                                <th class="text-center">Neto Dibayar<br>(Incl.PPN)</th>
                                                <th class="text-center">Sisa Neto<br>(Incl.PPN)</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody><?php echo $reconSourceTermin['rowsHtml']; ?></tbody>
                                        <tfoot>
                                            <tr style="background:#f1f5f9; font-weight:bold;">
                                                <td colspan="3" class="text-right">Total</td>
                                                <td class="text-right"><?php echo number_format($reconSourceTermin['totalDpp']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceTermin['totalPpn']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceTermin['totalIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceTermin['totalReturnedIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceTermin['totalNetIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceTermin['totalPaidIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceTermin['totalOutstandingIncl']); ?></td>
                                                <td class="text-center"><?php echo number_format($reconSourceTermin['count']); ?> row</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modal_recon_src_retensi" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"><i class="fa fa-shield text-warning"></i> Sumber Data Penagihan - Retensi</h4>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped no-margin">
                                        <thead>
                                            <tr style="background:#f8fafc;">
                                                <th width="40" class="text-center">No</th>
                                                <th>Tgl</th>
                                                <th>Nomer</th>
                                                <th class="text-right">DPP</th>
                                                <th class="text-right">PPN</th>
                                                <th class="text-center">Bruto<br>(Incl.PPN)</th>
                                                <th class="text-center">Retur/Pembatalan<br>(Incl.PPN)</th>
                                                <th class="text-center">Neto Ditagih<br>(Incl.PPN)</th>
                                                <th class="text-center">Neto Dibayar<br>(Incl.PPN)</th>
                                                <th class="text-center">Sisa Neto<br>(Incl.PPN)</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody><?php echo $reconSourceRetensi['rowsHtml']; ?></tbody>
                                        <tfoot>
                                            <tr style="background:#f1f5f9; font-weight:bold;">
                                                <td colspan="3" class="text-right">Total</td>
                                                <td class="text-right"><?php echo number_format($reconSourceRetensi['totalDpp']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceRetensi['totalPpn']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceRetensi['totalIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceRetensi['totalReturnedIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceRetensi['totalNetIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceRetensi['totalPaidIncl']); ?></td>
                                                <td class="text-right"><?php echo number_format($reconSourceRetensi['totalOutstandingIncl']); ?></td>
                                                <td class="text-center"><?php echo number_format($reconSourceRetensi['count']); ?> row</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Editor 4 Kolom Registry (MANUAL) -->
                <form id="form_editor_main" method="POST" action="<?php echo base_url(); ?>ToolCek/editRegistryProject?project_id=<?php echo $projectId; ?>" onsubmit="return validateBeforeSubmit();">
                    <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
                    <input type="hidden" name="action_save" value="1">

                    <div class="card">
                        <div class="card-title">
                            <i class="fa fa-pencil-square-o text-primary"></i> 1. Form Editor Manual 4 Kolom Registry
                            <div style="margin-left: auto; display: flex; gap: 8px;">
                                <button type="button" class="quick-btn quick-btn-move" onclick="autoMoveRetensiRow()" title="Deteksi baris 'RETENSI' di items3 dan pindahkan ke items5 secara otomatis">
                                    <i class="fa fa-exchange"></i> ⚡ Auto-Pindahkan 'RETENSI' dari Termin ke Garansi
                                </button>
                                <button type="button" class="quick-btn" onclick="formatAllJson()">
                                    <i class="fa fa-align-left"></i> Format / Rapikan JSON
                                </button>
                            </div>
                        </div>

                        <div class="grid-4">
                            <!-- Kolom ITEMS3 (TERMIN) -->
                            <div>
                                <label class="form-label" style="color: #b45309; display: flex; justify-content: space-between; align-items: center;">
                                    <span><i class="fa fa-table"></i> 1. ITEMS3 (TERMIN)</span>
                                    <span class="badge-custom badge-amber" id="badge_items3_count">0 Baris</span>
                                </label>
                                <div class="col-actions">
                                    <button type="button" class="btn-mini btn-mini-amber" onclick="addTerminRow()" title="Tambah 1 baris termin baru"><i class="fa fa-plus"></i> Baris</button>
                                    <button type="button" class="btn-mini btn-mini-amber" onclick="addPresetTermin(3)" title="Preset 3 tahap otomatis (DP 20%, Progres 70%, Retensi 10%)"><i class="fa fa-magic"></i> Preset 3 Tahap</button>
                                    <button type="button" class="btn-mini btn-mini-danger" onclick="clearColumnJson('items3_json')" title="Kosongkan JSON menjadi []"><i class="fa fa-trash-o"></i> []</button>
                                </div>
                                <textarea name="items3_json" id="items3_json" class="json-textarea" placeholder="[ { ... } ]"><?php echo htmlspecialchars(json_encode($decodedItems3, JSON_PRETTY_PRINT)); ?></textarea>
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    Tahapan termin (bobot progress %, nominal harga, nama termin).
                                </div>
                            </div>

                            <!-- Kolom ITEMS4 (UANG MUKA / DP) -->
                            <div>
                                <label class="form-label" style="color: #0369a1; display: flex; justify-content: space-between; align-items: center;">
                                    <span><i class="fa fa-money"></i> 2. ITEMS4 (UANG MUKA / DP)</span>
                                    <span class="badge-custom badge-blue" id="badge_items4_count">0 Baris</span>
                                </label>
                                <div class="col-actions">
                                    <button type="button" class="btn-mini btn-mini-primary" onclick="addDpTemplate(10)" title="Tambah DP 10% (otomatis hitung harga)"><i class="fa fa-plus"></i> DP 10%</button>
                                    <button type="button" class="btn-mini btn-mini-primary" onclick="addDpTemplate(20)" title="Tambah DP 20% (otomatis hitung harga)"><i class="fa fa-plus"></i> DP 20%</button>
                                    <button type="button" class="btn-mini btn-mini-primary" onclick="addDpTemplateCustom()" title="Tambah DP dengan persentase custom"><i class="fa fa-pencil"></i> Custom %</button>
                                    <button type="button" class="btn-mini btn-mini-danger" onclick="clearColumnJson('items4_json')" title="Kosongkan JSON menjadi []"><i class="fa fa-trash-o"></i> []</button>
                                </div>
                                <textarea name="items4_json" id="items4_json" class="json-textarea" placeholder="[ { ... } ]"><?php echo htmlspecialchars(json_encode($decodedItems4, JSON_PRETTY_PRINT)); ?></textarea>
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    Settingan Down Payment (DP) / Uang Muka project.
                                </div>
                            </div>

                            <!-- Kolom ITEMS5 (RETENSI / GARANSI) -->
                            <div>
                                <label class="form-label" style="color: #047857; display: flex; justify-content: space-between; align-items: center;">
                                    <span><i class="fa fa-shield"></i> 3. ITEMS5 (RETENSI / GARANSI)</span>
                                    <span class="badge-custom badge-green" id="badge_items5_count">0 Baris</span>
                                </label>
                                <div class="col-actions">
                                    <button type="button" class="btn-mini btn-mini-success" onclick="addRetensiTemplate(5)" title="Tambah Retensi 5% (otomatis hitung harga)"><i class="fa fa-plus"></i> Retensi 5%</button>
                                    <button type="button" class="btn-mini btn-mini-success" onclick="addRetensiTemplate(10)" title="Tambah Retensi 10% (otomatis hitung harga)"><i class="fa fa-plus"></i> Retensi 10%</button>
                                    <button type="button" class="btn-mini btn-mini-success" onclick="addRetensiTemplateCustom()" title="Tambah Retensi dengan persentase custom"><i class="fa fa-pencil"></i> Custom %</button>
                                    <button type="button" class="btn-mini btn-mini-danger" onclick="clearColumnJson('items5_json')" title="Kosongkan JSON menjadi []"><i class="fa fa-trash-o"></i> []</button>
                                </div>
                                <textarea name="items5_json" id="items5_json" class="json-textarea" placeholder="[ { ... } ]"><?php echo htmlspecialchars(json_encode($decodedItems5, JSON_PRETTY_PRINT)); ?></textarea>
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    Settingan Garansi / Retensi project (persen, harga, tgl_akhir).
                                </div>
                            </div>

                            <!-- Kolom ITEMS7 (INFORMASI KONTRAK / DATA PROJECT) -->
                            <div>
                                <label class="form-label" style="color: #6d28d9; display: flex; justify-content: space-between; align-items: center;">
                                    <span><i class="fa fa-file-text-o"></i> 4. ITEMS7 (INFORMASI KONTRAK)</span>
                                    <span class="badge-custom badge-purple" id="badge_items7_count">0 Baris</span>
                                </label>
                                <div class="col-actions">
                                    <button type="button" class="btn-mini btn-mini-purple" onclick="addKontrakTemplate()" title="Tambah template informasi kontrak"><i class="fa fa-plus"></i> Template Kontrak</button>
                                    <button type="button" class="btn-mini btn-mini-danger" onclick="clearColumnJson('items7_json')" title="Kosongkan JSON menjadi []"><i class="fa fa-trash-o"></i> []</button>
                                </div>
                                <textarea name="items7_json" id="items7_json" class="json-textarea" placeholder="[ { ... } ]"><?php echo htmlspecialchars(json_encode($decodedItems7, JSON_PRETTY_PRINT)); ?></textarea>
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    Informasi kontrak, syarat pembayaran & data pendukung project.
                                </div>
                            </div>
                        </div>

                        <!-- Opsi Tambahan Sinkronisasi -->
                        <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <label style="font-size: 12.5px; font-weight: 600; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="sync_payment_source" value="1" checked>
                                <span>Sinkronkan juga <code>transaksi_payment_source</code> (Target 7499 untuk Termin & Target 7488 untuk Retensi)</span>
                            </label>

                            <button type="submit" class="btn-tool btn-tool-success" style="padding: 10px 24px; font-size: 13.5px;">
                                <i class="fa fa-floppy-o"></i> SIMPAN HASIL FORM MANUAL KE 3 TRANSAKSI PROJECT
                            </button>
                        </div>
                    </div>
                </form>

                <?php if ($hasAnomaly) { ?>
                    <!-- PANEL REKOMENDASI AUTO-GENERATE & SIMULASI PENYESUAIAN REGISTRY -->
                    <div class="card" style="border: 2px solid #10b981; border-radius: 8px; box-shadow: 0 4px 16px rgba(16,185,129,0.12);">
                        <div class="card-title" style="color: #047857; font-size: 15px; border-bottom: 2px solid #d1fae5; padding-bottom: 10px;">
                            <i class="fa fa-magic text-success"></i> 2. Rekomendasi Solusi Otomatis Penyesuaian Registry
                            <span class="badge" style="background:#059669; color:#fff; margin-left:8px; font-size:11px;">Preview Mode (Belum Disimpan)</span>
                        </div>

                        <div style="font-size: 12px; color: #475569; margin-bottom: 14px; background: #ecfdf5; padding: 10px 14px; border-radius: 6px; border: 1px solid #a7f3d0;">
                            <i class="fa fa-lightbulb-o text-success" style="font-size: 14px;"></i> <strong>Logika Penyesuaian:</strong> Seluruh tagihan nyata yang telah terbit/dibayar dimasukkan ke <strong>Termin Progres (Rp <?php echo number_format($realTerminIncl); ?>)</strong>. Alokasi <strong>Uang Muka (DP)</strong> dikosongkan (0%) karena tidak pernah diterbitkan di modul termin proyek. Sisa nilai proyek yang belum tertagih (<strong>Rp <?php echo number_format(($simSumTerminDpp + $simSumRetensiDpp) * $divisorPpn - $realTerminIncl); ?></strong>) dialokasikan otomatis ke <?php echo ($simSumRetensiDpp > 0 && $simSumRetensiDpp == $simTotalProjectDpp - $simSumTerminDpp) ? '<strong>Retensi Garansi (items5)</strong>' : '<strong>Termin Pelunasan (items3)</strong>'; ?> sehingga total nilai kontrak tetap pas 100% dan status penagihan menjadi seimbang (Sisa Neto = 0).
                        </div>

                    <!-- Visual Progress Bar Simulasi -->
                    <div style="margin-bottom: 8px; font-weight: 600; color: #047857;">
                        <i class="fa fa-area-chart"></i> Simulasi Visualisasi Alokasi vs Penagihan Setelah Rekomendasi (Total: <?php echo number_format($simTotalProjectDpp); ?>):
                    </div>
                    <div style="display: flex; height: 28px; border-radius: 6px; overflow: hidden; margin-bottom: 16px; background: #e2e8f0;">
                        <?php if ($simDpPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#fff; font-size:11.5px; font-weight:bold; background:#5bc0de; width:<?php echo number_format($simDpPct, 2); ?>%;">
                                DP <?php echo number_format($simDpPct, 2); ?>%
                            </div>
                        <?php } ?>
                        <?php if ($simTerminIssuedPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#fff; font-size:11.5px; font-weight:bold; background:#5cb85c; width:<?php echo number_format($simTerminIssuedPct, 2); ?>%;">
                                Termin Terbit <?php echo number_format($simTerminIssuedPct, 2); ?>%
                            </div>
                        <?php } ?>
                        <?php if ($simRemainingPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#64748b; font-size:11.5px; font-weight:bold; background:#e2e8f0; width:<?php echo number_format($simRemainingPct, 2); ?>%;">
                                Sisa Termin <?php echo number_format($simRemainingPct, 2); ?>%
                            </div>
                        <?php } ?>
                        <?php if ($simRetensiPct > 0) { ?>
                            <div style="display:flex; align-items:center; justify-content:center; color:#fff; font-size:11.5px; font-weight:bold; background:#f0ad4e; width:<?php echo number_format($simRetensiPct, 2); ?>%;">
                                Retensi <?php echo number_format($simRetensiPct, 2); ?>%
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Table Simulasi Status Penagihan -->
                    <div class="table-responsive" style="margin-bottom: 18px;">
                        <table class="table-recon table table-bordered">
                            <thead>
                                <tr style="background: #f0fdf4;">
                                    <th>Komponen Biaya (Hasil Simulasi)</th>
                                    <th class="text-right">Alokasi (%)</th>
                                    <th class="text-right">Alokasi DPP</th>
                                    <th class="text-right">Alokasi PPN</th>
                                    <th class="text-center">Alokasi<br>(Incl.PPN)</th>
                                    <th class="text-center">Bruto Ditagih<br>(Incl.PPN)</th>
                                    <th class="text-center">Retur/Pembatalan<br>(Incl.PPN)</th>
                                    <th class="text-center">Neto Ditagih<br>(Incl.PPN)</th>
                                    <th class="text-center">Neto Dibayar<br>(Incl.PPN)</th>
                                    <th class="text-center">Sisa Neto<br>(Incl.PPN)</th>
                                    <th class="text-center" width="160">Status Simulasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row Uang Muka (DP) Hasil Simulasi -->
                                <tr>
                                    <td><strong>Uang Muka (DP)</strong></td>
                                    <td class="text-right"><?php echo number_format($simDpPct, 2); ?>%</td>
                                    <td class="text-right"><?php echo number_format($simDpSummary['alokasiDpp']); ?></td>
                                    <td class="text-right"><?php echo number_format($simDpSummary['alokasiPpn']); ?></td>
                                    <td class="text-right"><?php echo number_format($simDpSummary['alokasiIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simDpSummary['brutoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simDpSummary['returIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simDpSummary['netoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simDpSummary['netoDibayarIncl']); ?></td>
                                    <td class="text-right <?php echo $simDpSummary['sisaNetoIncl'] > 0 ? 'text-red-bold' : 'text-green-bold'; ?>">
                                        <?php echo number_format($simDpSummary['sisaNetoIncl']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($simDpSummary['sisaNetoIncl'] == 0) { ?>
                                            <span class="badge" style="background:#059669; color:#fff;">✅ Nol (Seimbang)</span>
                                        <?php } else { ?>
                                            <span class="badge" style="background:#0284c7; color:#fff;">Sisa Nilai Proyek (Menunggu Diterbitkan)</span>
                                        <?php } ?>
                                    </td>
                                </tr>

                                <!-- Row Termin Progres Hasil Simulasi -->
                                <tr>
                                    <td><strong>Termin Progres</strong></td>
                                    <td class="text-right"><?php echo number_format($simTerminAllocPct, 2); ?>%</td>
                                    <td class="text-right"><?php echo number_format($simTerminSummary['alokasiDpp']); ?></td>
                                    <td class="text-right"><?php echo number_format($simTerminSummary['alokasiPpn']); ?></td>
                                    <td class="text-right"><?php echo number_format($simTerminSummary['alokasiIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simTerminSummary['brutoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simTerminSummary['returIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simTerminSummary['netoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simTerminSummary['netoDibayarIncl']); ?></td>
                                    <td class="text-right <?php echo $simTerminSummary['sisaNetoIncl'] > 0 ? 'text-red-bold' : 'text-green-bold'; ?>">
                                        <?php echo number_format($simTerminSummary['sisaNetoIncl']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($simTerminSummary['sisaNetoIncl'] == 0) { ?>
                                            <span class="badge" style="background:#059669; color:#fff;">✅ Pas / Lunas (0)</span>
                                        <?php } else { ?>
                                            <span class="badge" style="background:#0284c7; color:#fff;">Ada Sisa</span>
                                        <?php } ?>
                                    </td>
                                </tr>

                                <!-- Row Retensi Hasil Simulasi -->
                                <tr>
                                    <td><strong>Retensi</strong></td>
                                    <td class="text-right"><?php echo number_format($simRetensiPct, 2); ?>%</td>
                                    <td class="text-right"><?php echo number_format($simRetensiSummary['alokasiDpp']); ?></td>
                                    <td class="text-right"><?php echo number_format($simRetensiSummary['alokasiPpn']); ?></td>
                                    <td class="text-right"><?php echo number_format($simRetensiSummary['alokasiIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simRetensiSummary['brutoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simRetensiSummary['returIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simRetensiSummary['netoDitagihIncl']); ?></td>
                                    <td class="text-right"><?php echo number_format($simRetensiSummary['netoDibayarIncl']); ?></td>
                                    <td class="text-right <?php echo $simRetensiSummary['sisaNetoIncl'] > 0 ? 'text-red-bold' : 'text-green-bold'; ?>">
                                        <?php echo number_format($simRetensiSummary['sisaNetoIncl']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($simSumRetensiDpp <= 0) { ?>
                                            <small style="color:#64748b;">(Tidak Ada Retensi)</small>
                                        <?php } else { ?>
                                            <span class="badge" style="background:#f59e0b; color:#fff;">Retensi Terjadwal</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f0fdf4; font-weight: bold;">
                                    <td colspan="9" class="text-right">Total Sisa Saldo Simulasi Di Modul Termin (Incl.PPN):</td>
                                    <td colspan="2" style="font-size: 13.5px; color: <?php echo $simTotalSisaNetoIncl > 0 ? '#0284c7' : '#059669'; ?>;">
                                        Rp <?php echo number_format($simTotalSisaNetoIncl); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Form Input JSON Rekomendasi (Editable / Ready to copy) -->
                    <div style="font-weight: 700; color: #047857; margin-bottom: 8px;">
                        <i class="fa fa-code"></i> Draf Array Rekomendasi:
                    </div>
                    <div class="grid-4">
                        <!-- Kolom ITEMS3 (TERMIN REKOMENDASI) -->
                        <div>
                            <label class="form-label" style="color: #b45309;">
                                <i class="fa fa-table"></i> 1. REKOMENDASI ITEMS3 (TERMIN)
                                <span class="badge-custom badge-amber pull-right" id="badge_rec_items3_count"><?php echo count($recItems3); ?> Baris</span>
                            </label>
                            <textarea id="rec_items3_json" class="json-textarea json-textarea-rec"><?php echo htmlspecialchars(json_encode($recItems3, JSON_PRETTY_PRINT)); ?></textarea>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                Disesuaikan persis dengan nominal invoice/kwitansi termin yang sudah dibayar.
                            </div>
                        </div>

                        <!-- Kolom ITEMS4 (DP REKOMENDASI) -->
                        <div>
                            <label class="form-label" style="color: #0369a1;">
                                <i class="fa fa-money"></i> 2. REKOMENDASI ITEMS4 (DP)
                                <span class="badge-custom badge-blue pull-right" id="badge_rec_items4_count"><?php echo count($recItems4); ?> Baris</span>
                            </label>
                            <textarea id="rec_items4_json" class="json-textarea json-textarea-rec"><?php echo htmlspecialchars(json_encode($recItems4, JSON_PRETTY_PRINT)); ?></textarea>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                Menampung sisa nilai proyek yang belum diterbitkan penagihannya.
                            </div>
                        </div>

                        <!-- Kolom ITEMS5 (RETENSI REKOMENDASI) -->
                        <div>
                            <label class="form-label" style="color: #047857;">
                                <i class="fa fa-shield"></i> 3. REKOMENDASI ITEMS5 (RETENSI)
                                <span class="badge-custom badge-green pull-right" id="badge_rec_items5_count"><?php echo count($recItems5); ?> Baris</span>
                            </label>
                            <textarea id="rec_items5_json" class="json-textarea json-textarea-rec"><?php echo htmlspecialchars(json_encode($recItems5, JSON_PRETTY_PRINT)); ?></textarea>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                Draf Garansi / Retensi proyek.
                            </div>
                        </div>

                        <!-- Kolom ITEMS7 (KONTRAK REKOMENDASI) -->
                        <div>
                            <label class="form-label" style="color: #6d28d9;">
                                <i class="fa fa-file-text-o"></i> 4. REKOMENDASI ITEMS7 (KONTRAK)
                                <span class="badge-custom badge-purple pull-right" id="badge_rec_items7_count"><?php echo count($recItems7); ?> Baris</span>
                            </label>
                            <textarea id="rec_items7_json" class="json-textarea json-textarea-rec"><?php echo htmlspecialchars(json_encode($recItems7, JSON_PRETTY_PRINT)); ?></textarea>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                Draf informasi kontrak & syarat pembayaran.
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Terapkan & Simpan Rekomendasi -->
                    <div style="margin-top: 20px; padding-top: 16px; border-top: 2px dashed #a7f3d0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <button type="button" class="btn-tool btn-tool-primary" onclick="applyRecommendationToMainForm();" style="padding: 10px 20px; font-size: 13px;">
                            <i class="fa fa-arrow-up"></i> 📥 SALIN REKOMENDASI KE FORM EDITOR DI ATAS
                        </button>

                        <form method="POST" action="<?php echo base_url(); ?>ToolCek/editRegistryProject?project_id=<?php echo $projectId; ?>" onsubmit="return confirmSaveRecommendation();" style="margin: 0;">
                            <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
                            <input type="hidden" name="action_save" value="1">
                            <input type="hidden" name="sync_payment_source" value="1">
                            <input type="hidden" name="items3_json" id="direct_rec_items3">
                            <input type="hidden" name="items4_json" id="direct_rec_items4">
                            <input type="hidden" name="items5_json" id="direct_rec_items5">
                            <input type="hidden" name="items7_json" id="direct_rec_items7">

                            <button type="submit" class="btn-tool btn-tool-success" style="padding: 10px 24px; font-size: 13.5px; background:#047857;">
                                <i class="fa fa-check-circle"></i> 💾 SIMPAN REKOMENDASI LANGSUNG KE 3 TRANSAKSI
                            </button>
                        </form>
                    </div>
                </div>
                <?php } ?>
            <?php } ?>
        </div>

        <script>
            // Salin Rekomendasi ke Form Editor Utama di Atas
            function applyRecommendationToMainForm() {
                try {
                    document.getElementById('items3_json').value = document.getElementById('rec_items3_json').value;
                    document.getElementById('items4_json').value = document.getElementById('rec_items4_json').value;
                    document.getElementById('items5_json').value = document.getElementById('rec_items5_json').value;
                    document.getElementById('items7_json').value = document.getElementById('rec_items7_json').value;

                    updateCounters();
                    formatAllJson();

                    $('html, body').animate({
                        scrollTop: $("#form_editor_main").offset().top - 20
                    }, 400);

                    alert('✅ Rekomendasi Auto-Generate telah disalin ke Form Editor di atas!\nSilakan periksa kembali sebelum menekan tombol "Simpan".');
                } catch(e) {
                    alert('Gagal menyalin rekomendasi: ' + e.message);
                }
            }

            // Konfirmasi Simpan Langsung Rekomendasi
            function confirmSaveRecommendation() {
                try {
                    document.getElementById('direct_rec_items3').value = document.getElementById('rec_items3_json').value;
                    document.getElementById('direct_rec_items4').value = document.getElementById('rec_items4_json').value;
                    document.getElementById('direct_rec_items5').value = document.getElementById('rec_items5_json').value;
                    document.getElementById('direct_rec_items7').value = document.getElementById('rec_items7_json').value;

                    return confirm('Apakah Anda yakin ingin LANGSUNG MENERAPKAN & MENYIMPAN Rekomendasi Auto-Generate ini?\nPerubahan akan langsung mengupdate 3 transaksi project (588so, 588st, 588spo) dan mensinkronkan payment source.');
                } catch(e) {
                    alert('Error: ' + e.message);
                    return false;
                }
            }

            // Update counter badges saat load
            function updateCounters() {
                try {
                    var itm3 = JSON.parse(document.getElementById('items3_json').value || '[]');
                    document.getElementById('badge_items3_count').innerText = (Array.isArray(itm3) ? itm3.length : (typeof itm3 === 'object' && itm3 !== null ? Object.keys(itm3).length : 0)) + ' Baris';
                } catch(e) { document.getElementById('badge_items3_count').innerText = 'Invalid JSON'; }

                try {
                    var itm4 = JSON.parse(document.getElementById('items4_json').value || '[]');
                    document.getElementById('badge_items4_count').innerText = (Array.isArray(itm4) ? itm4.length : (typeof itm4 === 'object' && itm4 !== null ? Object.keys(itm4).length : 0)) + ' Baris';
                } catch(e) { document.getElementById('badge_items4_count').innerText = 'Invalid JSON'; }

                try {
                    var itm5 = JSON.parse(document.getElementById('items5_json').value || '[]');
                    document.getElementById('badge_items5_count').innerText = (Array.isArray(itm5) ? itm5.length : (typeof itm5 === 'object' && itm5 !== null ? Object.keys(itm5).length : 0)) + ' Baris';
                } catch(e) { document.getElementById('badge_items5_count').innerText = 'Invalid JSON'; }

                try {
                    var itm7 = JSON.parse(document.getElementById('items7_json').value || '[]');
                    document.getElementById('badge_items7_count').innerText = (Array.isArray(itm7) ? itm7.length : (typeof itm7 === 'object' && itm7 !== null ? Object.keys(itm7).length : 0)) + ' Baris';
                } catch(e) { document.getElementById('badge_items7_count').innerText = 'Invalid JSON'; }
            }

            // Auto Move Retensi Row dari items3 ke items5
            function autoMoveRetensiRow() {
                try {
                    var raw3 = document.getElementById('items3_json').value.trim();
                    var raw5 = document.getElementById('items5_json').value.trim();
                    var itm3 = raw3 ? JSON.parse(raw3) : [];
                    var itm5 = raw5 ? JSON.parse(raw5) : [];

                    if (!Array.isArray(itm3)) { itm3 = []; }
                    if (!Array.isArray(itm5)) { itm5 = []; }

                    var newItm3 = [];
                    var movedRows = [];

                    for (var i = 0; i < itm3.length; i++) {
                        var row = itm3[i];
                        var nama = (row.nama || '').toString().toUpperCase();
                        if (nama.indexOf('RETENSI') !== -1) {
                            // Siapkan format standar untuk items5 (Retensi/Garansi)
                            var retensiObj = {
                                "harga_project": row.harga_project || "<?php echo isset($projectData->harga) ? $projectData->harga : 0; ?>",
                                "persen": (row.persen || row.progress || "10").toString(),
                                "harga": (row.harga || 0).toString(),
                                "tgl_akhir_garansi": row.tgl_akhir_garansi || "2026-12-31",
                                "keterangan_garansi": row.keterangan_garansi || ""
                            };
                            movedRows.push(retensiObj);
                        } else {
                            newItm3.push(row);
                        }
                    }

                    if (movedRows.length === 0) {
                        alert('Tidak ditemukan baris bertuliskan "RETENSI" pada ITEMS3.');
                        return;
                    }

                    // Gabungkan ke items5
                    for (var j = 0; j < movedRows.length; j++) {
                        itm5.push(movedRows[j]);
                    }

                    document.getElementById('items3_json').value = JSON.stringify(newItm3, null, 4);
                    document.getElementById('items5_json').value = JSON.stringify(itm5, null, 4);
                    updateCounters();

                    alert('Berhasil memindahkan ' + movedRows.length + ' baris Retensi dari ITEMS3 (Termin) ke ITEMS5 (Retensi)! Silakan periksa hasilnya dan klik Simpan.');
                } catch(e) {
                    alert('Gagal memproses JSON: ' + e.message);
                }
            }

            var projectHargaDpp = <?php echo isset($projectData->harga) ? (float)$projectData->harga : 0; ?>;
            var divisorPpn = <?php echo isset($divisorPpn) ? (float)$divisorPpn : 1.11; ?>;
            var projectRefKontrak = "<?php echo isset($projectData->transaksi_no) ? addslashes($projectData->transaksi_no) : (isset($projectData->quot_nomer) ? addslashes($projectData->quot_nomer) : ''); ?>";

            // Kosongkan Kolom JSON
            function clearColumnJson(id) {
                if (confirm('Kosongkan kolom ini menjadi array kosong []?')) {
                    document.getElementById(id).value = '[]';
                    updateCounters();
                }
            }

            // Tambah Template DP (items4)
            function addDpTemplate(persen) {
                try {
                    var raw = document.getElementById('items4_json').value.trim();
                    var itm4 = raw ? JSON.parse(raw) : [];
                    if (!Array.isArray(itm4)) { itm4 = []; }

                    var pct = parseFloat(persen) || 10;
                    var dpDpp = (projectHargaDpp * pct) / 100;
                    var dpIncl = Math.round(dpDpp * divisorPpn);

                    itm4.push({
                        "harga_project": projectHargaDpp.toString(),
                        "persen": pct.toString(),
                        "harga": dpIncl.toString(),
                        "keterangan_dp": ""
                    });

                    document.getElementById('items4_json').value = JSON.stringify(itm4, null, 4);
                    updateCounters();
                } catch(e) {
                    alert('Gagal menambah template DP: ' + e.message);
                }
            }

            function addDpTemplateCustom() {
                var inputPct = prompt('Masukkan persentase Down Payment (DP) (contoh: 15 atau 20):', '20');
                if (inputPct !== null && inputPct.trim() !== '') {
                    var pct = parseFloat(inputPct);
                    if (isNaN(pct) || pct <= 0 || pct > 100) {
                        alert('Persentase tidak valid! Harap masukkan angka antara 1 s.d 100.');
                        return;
                    }
                    addDpTemplate(pct);
                }
            }

            // Tambah Template Retensi (items5)
            function addRetensiTemplate(persen) {
                try {
                    var raw = document.getElementById('items5_json').value.trim();
                    var itm5 = raw ? JSON.parse(raw) : [];
                    if (!Array.isArray(itm5)) { itm5 = []; }

                    var pct = parseFloat(persen) || 10;
                    var retDpp = (projectHargaDpp * pct) / 100;
                    var retIncl = Math.round(retDpp * divisorPpn);
                    var currentYear = new Date().getFullYear();

                    itm5.push({
                        "harga_project": projectHargaDpp.toString(),
                        "persen": pct.toString(),
                        "harga": retIncl.toString(),
                        "tgl_akhir_garansi": currentYear + "-12-31",
                        "keterangan_garansi": "RETENSI"
                    });

                    document.getElementById('items5_json').value = JSON.stringify(itm5, null, 4);
                    updateCounters();
                } catch(e) {
                    alert('Gagal menambah template Retensi: ' + e.message);
                }
            }

            function addRetensiTemplateCustom() {
                var inputPct = prompt('Masukkan persentase Retensi / Garansi (contoh: 5 atau 10):', '5');
                if (inputPct !== null && inputPct.trim() !== '') {
                    var pct = parseFloat(inputPct);
                    if (isNaN(pct) || pct <= 0 || pct > 100) {
                        alert('Persentase tidak valid! Harap masukkan angka antara 1 s.d 100.');
                        return;
                    }
                    addRetensiTemplate(pct);
                }
            }

            // Tambah Baris Termin (items3)
            function addTerminRow() {
                try {
                    var raw = document.getElementById('items3_json').value.trim();
                    var itm3 = raw ? JSON.parse(raw) : [];
                    if (!Array.isArray(itm3)) { itm3 = []; }

                    var nextUrut = itm3.length + 1;
                    itm3.push({
                        "urut": nextUrut.toString(),
                        "harga_project": projectHargaDpp.toString(),
                        "nama": "TERMIN " + nextUrut,
                        "progress": "0",
                        "persen": "0",
                        "harga": "0"
                    });

                    document.getElementById('items3_json').value = JSON.stringify(itm3, null, 4);
                    updateCounters();
                } catch(e) {
                    alert('Gagal menambah baris termin: ' + e.message);
                }
            }

            // Preset Termin 3 Tahap
            function addPresetTermin(type) {
                if (!confirm('Terapkan preset termin 3 tahap (DP 20%, Progres 70%, Retensi 10%)? Ini akan menimpa ITEMS3, ITEMS4, dan ITEMS5.')) {
                    return;
                }
                try {
                    var dpPct = 20;
                    var terminPct = 70;
                    var retensiPct = 10;

                    var dpIncl = Math.round(((projectHargaDpp * dpPct) / 100) * divisorPpn);
                    var terminIncl = Math.round(((projectHargaDpp * terminPct) / 100) * divisorPpn);
                    var retensiIncl = Math.round(((projectHargaDpp * retensiPct) / 100) * divisorPpn);
                    var currentYear = new Date().getFullYear();

                    var itm4 = [{
                        "harga_project": projectHargaDpp.toString(),
                        "persen": dpPct.toString(),
                        "harga": dpIncl.toString(),
                        "keterangan_dp": ""
                    }];

                    var itm3 = [{
                        "urut": "1",
                        "harga_project": projectHargaDpp.toString(),
                        "nama": "TERMIN 1 (PROGRES 100%)",
                        "progress": "100",
                        "persen": terminPct.toString(),
                        "harga": terminIncl.toString()
                    }];

                    var itm5 = [{
                        "harga_project": projectHargaDpp.toString(),
                        "persen": retensiPct.toString(),
                        "harga": retensiIncl.toString(),
                        "tgl_akhir_garansi": currentYear + "-12-31",
                        "keterangan_garansi": "RETENSI"
                    }];

                    document.getElementById('items4_json').value = JSON.stringify(itm4, null, 4);
                    document.getElementById('items3_json').value = JSON.stringify(itm3, null, 4);
                    document.getElementById('items5_json').value = JSON.stringify(itm5, null, 4);
                    updateCounters();
                } catch(e) {
                    alert('Gagal menerapkan preset: ' + e.message);
                }
            }

            // Tambah Template Kontrak (items7)
            function addKontrakTemplate() {
                try {
                    var raw = document.getElementById('items7_json').value.trim();
                    var itm7 = raw ? JSON.parse(raw) : [];
                    if (!Array.isArray(itm7)) { itm7 = []; }

                    var today = new Date().toISOString().slice(0, 10);

                    itm7.push({
                        "ref_kontrak": projectRefKontrak || "KONTRAK-PROJECT",
                        "tgl_kontrak": today,
                        "tgl_mulai_project": today,
                        "tgl_akhir_project": today,
                        "notes_project": ""
                    });

                    document.getElementById('items7_json').value = JSON.stringify(itm7, null, 4);
                    updateCounters();
                } catch(e) {
                    alert('Gagal menambah template kontrak: ' + e.message);
                }
            }

            // Rapikan JSON
            function formatAllJson() {
                try {
                    var f3 = document.getElementById('items3_json');
                    if (f3 && f3.value.trim()) { f3.value = JSON.stringify(JSON.parse(f3.value), null, 4); }
                    var f4 = document.getElementById('items4_json');
                    if (f4 && f4.value.trim()) { f4.value = JSON.stringify(JSON.parse(f4.value), null, 4); }
                    var f5 = document.getElementById('items5_json');
                    if (f5 && f5.value.trim()) { f5.value = JSON.stringify(JSON.parse(f5.value), null, 4); }
                    var f7 = document.getElementById('items7_json');
                    if (f7 && f7.value.trim()) { f7.value = JSON.stringify(JSON.parse(f7.value), null, 4); }
                    updateCounters();
                } catch(e) {
                    alert('Format JSON tidak valid: ' + e.message);
                }
            }

            // Validasi sebelum submit
            function validateBeforeSubmit() {
                try {
                    var f3 = document.getElementById('items3_json').value.trim();
                    if (f3) { JSON.parse(f3); }
                } catch(e) {
                    alert('Format JSON ITEMS3 tidak valid: ' + e.message);
                    return false;
                }

                try {
                    var f4 = document.getElementById('items4_json').value.trim();
                    if (f4) { JSON.parse(f4); }
                } catch(e) {
                    alert('Format JSON ITEMS4 tidak valid: ' + e.message);
                    return false;
                }

                try {
                    var f5 = document.getElementById('items5_json').value.trim();
                    if (f5) { JSON.parse(f5); }
                } catch(e) {
                    alert('Format JSON ITEMS5 tidak valid: ' + e.message);
                    return false;
                }

                try {
                    var f7 = document.getElementById('items7_json').value.trim();
                    if (f7) { JSON.parse(f7); }
                } catch(e) {
                    alert('Format JSON ITEMS7 tidak valid: ' + e.message);
                    return false;
                }

                return confirm('Apakah Anda yakin ingin menyimpan perubahan array registry ini?\nPerubahan akan langsung diterapkan ke seluruh transaksi project terkait.');
            }

            window.onload = function() {
                if (document.getElementById('items3_json')) {
                    updateCounters();
                    document.getElementById('items3_json').addEventListener('input', updateCounters);
                    document.getElementById('items4_json').addEventListener('input', updateCounters);
                    document.getElementById('items5_json').addEventListener('input', updateCounters);
                    if (document.getElementById('items7_json')) {
                        document.getElementById('items7_json').addEventListener('input', updateCounters);
                    }
                }
            };
        </script>
        </body>
        </html>
        <?php
    }
    // END OF COMPLETE REPEATED LOGIC
}
