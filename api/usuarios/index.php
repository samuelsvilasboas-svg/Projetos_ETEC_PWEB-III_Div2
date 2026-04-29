<?php
require '../../app/conexao.php';
$pdo = Conexao::conectar();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
/*
?jsn={"op":"i","id":0,"nome":"","login":"","senha":"","logado":true}
*/
$json = filter_input(INPUT_GET, 'jsn');
$data = json_decode($json, true);
/*op=i insert - op=u update - op=d delete - op=l login - op=sp consulta com parametro*/
$op = $data['op'];
$id = $data['id'];
$nome = $data['nome'];
$login = $data['login'];
$senha = $data['senha'];
$logado = $data['logado'];

switch ($op) {
    case 'i':
        $sql = "insert into usuarios (usunome,usulogin,ususenha) values (?,?,MD5(?));";
        $prp = $pdo->prepare($sql);
        $prp->execute([$nome, $login, $senha]);
        break;
    case 'u':
        if (empty($data['senha'])) {
            $sql = "update usuarios set usunome=?,usulogin=?, usulogado=? where usuid=?;";
            $prp = $pdo->prepare($sql);
            $prp->execute([$nome, $login, $logado, $id]);
        } else {
            $sql = "update usuarios set usunome=?,usulogin=?,ususenha=MD5(?), usulogado=? where usuid=?;";
            $prp = $pdo->prepare($sql);
            $prp->execute([$nome, $login, $senha, $logado, $id]);
        }
        break;
    case 'd':
        $sql = "delete from usuarios where usuid=?;";
        $prp = $pdo->prepare($sql);
        $prp->execute([$id]);
        break;
    case 'l':
        $sql = "
select 
    usuid as id,
	usunome as nome,
	usulogin as usuario,
	usulogado as logado
from 
    usuarios
where 
   usulogin = ? 
and 
    ususenha = MD5(?);";
        $prp = $pdo->prepare($sql);
        $prp->execute([$usuario, $senha]);
        $data = $prp->fetchall(PDO::FETCH_ASSOC);
        echo json_encode($data);
        break;
    case 'sp':
        $nome = '%' . $nome . '%';
        $sql = "
select 
    usuid as id,
	usunome as nome,
	usulogin as usuario,
	usulogado as logado
from 
    usuarios
where 
    usunome like ?;
";
        $prp = $pdo->prepare($sql);
        $prp->execute([$nome]);
        $data = $prp->fetchall(PDO::FETCH_ASSOC);
        echo json_encode($data);
        break;
    default:
        echo 'coloca o parametro certo seu ameba';
        break;
}
Conexao::desconectar();
