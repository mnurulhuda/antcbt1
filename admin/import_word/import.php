<?php
require "func.php";

function import($file) {
    $folder = 'soal/';
    $folder_gambar = '../files/';
    
    $question_split = "/Soal:[0-9]+\)/";
    $option_split = "/[A-Z]:/";
    $correct_split = "/Kunci:/";

    // inisialisasi untuk pembuatan file .zip
    $info = pathinfo($file);
    $new_name = $info['filename'] . '.Zip';
    $nama_file_zip = $folder . $new_name;
    rename($file, $nama_file_zip);

    // membuat file zip baru dari file .docx
    $zip = new ZipArchive;

    if ($zip->open($nama_file_zip) === true) {

        // extract file zip di folder soal
        $zip->extractTo($folder);
        $zip->close();

        $word_xml = $folder . "word/document.xml";
        $word_xml_relational = $folder . "word/_rels/document.xml.rels";

        $content = file_get_contents($word_xml);

        // htmlentities -> mengubah tag html menjadi karakter (special)
        // strip_tags -> menghilangkan tag html
        $content = htmlentities(strip_tags($content, "<a:blip>"));

        // mbuh gak ngerti maksud'e
        // nek gak salah mengambil file - file relasi
        $xml = simplexml_load_file($word_xml_relational);

        // file gambar yang didukung
        $supported_image = array(
            'gif',
            'jpg',
            'jpeg',
            'png',
        );

        // pengambilan file gambar
        $relation_image = array();
        foreach ($xml as $key => $qjd) {
            //echo "<pre>";
            //print_r($qjd);

            // mengambil nama file yang terelasi
            $ext = strtolower(pathinfo($qjd['Target'], PATHINFO_EXTENSION));
            //echo $ext."<br>";

            // jika ada file gambar ('gif', 'jpg', 'jpeg', 'png')
            if (in_array($ext, $supported_image)) {

                // menyimpan id dan nama file gambar dari attribut ke dalam varibel
                $id = xml_attribute($qjd, 'Id');
                $target = xml_attribute($qjd, 'Target');

                $relation_image[$id] = $target;
                //print_r($qjd['Id']); echo "<-->";
                //echo $qjd['Id']."<-->";
                //echo $qjd['Target']."<br>";
            }
        }

        $word_folder = $folder . "word";
        $prop_folder = $folder . "docProps";
        $relat_folder = $folder . "_rels";
        $content_folder = $folder . "[Content_Types].xml";

        $rand_inc_number = 1;
        foreach ($relation_image as $key => $value) {
            $rplc_str = '&lt;a:blip r:embed=&quot;' . $key . '&quot; cstate=&quot;print&quot;&gt;&lt;/a:blip&gt;';
            $rplc_str2 = '&lt;a:blip r:embed=&quot;' . $key . '&quot;&gt;&lt;/a:blip&gt;';
            $rplc_str3 = '&lt;a:blip r:embed=&quot;' . $key . '&quot;/&gt;';

            $ext_img = strtolower(pathinfo($value, PATHINFO_EXTENSION));
            $imagenew_name = time() . $rand_inc_number . "." . $ext_img;
            $old_path = $word_folder . "/" . $value;
            $new_path = $folder_gambar . $imagenew_name;

            // memindah file gambar ke dalam folder soal ($new_path)
            rename($old_path, $new_path);
            $img = '<img src="' . '../../files/' . $imagenew_name . '">';
            // echo $rplc_str2."--".htmlentities($img);

            // replace kode xml dengan tag html
            $content = str_replace($rplc_str, $img, $content);
            $content = str_replace($rplc_str2, $img, $content);
            $content = str_replace($rplc_str3, $img, $content);
            $rand_inc_number++;
        }

        // menghapus semua file sementara
        rrmdir($word_folder);
        rrmdir($relat_folder);
        rrmdir($prop_folder);
        rrmdir($content_folder);
        rrmdir($nama_file_zip);

        $content2 = $content;
        $expl = array_filter(preg_split($question_split, $content)); // memecah Karakter Q: dengan nomor soal

        if (trim($expl[0]) == '') { // menghilangkan angka nol
            unset($expl[0]);
        }
        $expl = array_values($expl); // $expl = Q:1
        $explflag = get_numerics($content2); // mengambil angka/nomor soal dari masing2 soal

        $soal = array();

        foreach ($expl as $ekey => $value) { // looping berdasarkan jumlah soal
            $cqno = str_replace('Soal:', '', $explflag[$ekey]); // menghilangkan Q: dari nomor soal -> $cqno = nomor soal

            // memecah soal dan pilihan jawaban menjadi array
            $quesions[] = array_filter(preg_split($option_split, $value));

            // loop data soal
            foreach ($quesions as $key => $options) {
                $option_count = count($options);
                $option = array();

                foreach ($options as $key_option => $val_option) {
                    if ($key_option == ($option_count - 1)) { // pilihan jawaban terakhir
                        if (preg_match($correct_split, $val_option, $match)) {

                            // memecah antara pilihan jawaban terakhir dengan kunci jawaban
                            $correct = array_filter(preg_split($correct_split, $val_option));
                            $options[$key_option] = $correct['0']; // $option = pilihan jawaban terakhir

                            $options[$option_count] = $correct['1']; // value dari kunci jawaban
                        } else { // selain pilihan jawaban terakhir (tidak ada keyword kunci:)
                            $options[$key_option] = $val_option;
                            $options[$option_count] = "";
                        }
                    }

                    // $options[$key_option] = str_replace('"','&#34;', $options[$key_option] );
                    $options[$key_option] = str_replace("‘",'&#39;', $options[$key_option] );
                    $options[$key_option] = str_replace("’",'&#39;', $options[$key_option] );
                    $options[$key_option] = str_replace("â€œ",'&#34;', $options[$key_option] );
                    $options[$key_option] = str_replace("â€˜",'&#39;', $options[$key_option] );
                    $options[$key_option] = str_replace("â€™",'&#39;', $options[$key_option] );
                    $options[$key_option] = str_replace("â€",'&#34;', $options[$key_option] );
                    $options[$key_option] = str_replace("'","&#39;", $options[$key_option] );
                    $options[$key_option] = str_replace("\n","<br>", $options[$key_option] );

                    $options[$key_option] = str_replace("&amp;lt;", "<", $options[$key_option] );
                    $options[$key_option] = str_replace("&amp;gt;", ">", $options[$key_option] );
                    $options[$key_option] = str_replace("'", "&#39;", $options[$key_option] );
                    // $options[$key_option] = str_replace(" ", "", $options[$key_option] );
                    $options[$key_option] = str_replace(" &ndash;", "-", $options[$key_option] );
                    
                }
            }
            $soal[$cqno] = $options;
        }
    }
    return $soal;
}