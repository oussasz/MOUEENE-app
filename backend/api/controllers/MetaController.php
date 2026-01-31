<?php

class MetaController {
    public function version(Request $req): void {
        $commit = null;
        $commitFile = __DIR__ . '/../../../.git/HEAD';

        // If deployed without .git, this will just be null.
        if (file_exists($commitFile)) {
            $head = trim((string)file_get_contents($commitFile));
            if (strpos($head, 'ref:') === 0) {
                $ref = trim(substr($head, 4));
                $refFile = __DIR__ . '/../../../.git/' . $ref;
                if (file_exists($refFile)) {
                    $commit = trim((string)file_get_contents($refFile));
                }
            } else {
                $commit = $head;
            }
        }

        Response::success([
            'name' => 'Moueene API',
            'version' => 'v1',
            'commit' => $commit,
            'server_time' => date('c'),
        ], 'OK');
    }
}
