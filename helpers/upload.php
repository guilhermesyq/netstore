<?php

function salvarImagemJogo(
    string $tmpName,
    int $size,
    int $error,
    string $uploadDir,
    string $publicPrefix,
    string $baseName,
    array &$arquivosSalvos
): string {
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erro no upload da imagem.');
    }

    if ($size > 5 * 1024 * 1024) {
        throw new RuntimeException('Cada imagem deve ter no máximo 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);

    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!isset($extensoes[$mime])) {
        throw new RuntimeException('Formato inválido. Envie apenas JPG, PNG, WEBP ou GIF.');
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $nomeArquivo = $baseName . '.' . $extensoes[$mime];
    $destino = $uploadDir . '/' . $nomeArquivo;

    if (!move_uploaded_file($tmpName, $destino)) {
        throw new RuntimeException('Não foi possível salvar a imagem no servidor.');
    }

    $arquivosSalvos[] = $destino;
    return $publicPrefix . $nomeArquivo;
}

function apagarArquivoImagemJogo(?string $url): void {
    if (!$url) {
        return;
    }

    $prefixos = [
        '/netstore/uploads/jogos/',
        '/netstore/assets/jogos/', // compatibilidade com registros antigos
    ];

    $relativo = null;
    foreach ($prefixos as $prefixo) {
        if (str_starts_with($url, $prefixo)) {
            $relativo = substr($url, strlen($prefixo));
            break;
        }
    }

    if ($relativo === null) {
        return;
    }

    $baseDir = realpath(dirname(__DIR__) . '/uploads/jogos');
    if ($baseDir === false) {
        return;
    }

    $caminho = realpath($baseDir . DIRECTORY_SEPARATOR . $relativo);
    if ($caminho !== false && str_starts_with($caminho, $baseDir . DIRECTORY_SEPARATOR) && is_file($caminho)) {
        @unlink($caminho);
    }
}
