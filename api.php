<?php
/**
 * API REST - Slim Framework
 * Endpoints numerati da 1 a 10
 * Database: Fornitori / Pezzi / Catalogo
 */

require 'vendor/autoload.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

// Dati mock per il testing (sincronizzati con database.sql)
$mockData = [
    'fornitori' => [
        ['fid' => 'F01', 'fnome' => 'Acme', 'indirizzo' => 'Via Roma 1, Milano'],
        ['fid' => 'F02', 'fnome' => 'WidgetCorp', 'indirizzo' => 'Via Milano 2, Torino'],
        ['fid' => 'F03', 'fnome' => 'Supplies Inc', 'indirizzo' => 'Via Torino 3, Genova'],
        ['fid' => 'F04', 'fnome' => 'TechParts', 'indirizzo' => 'Via Venezia 4, Venezia'],
        ['fid' => 'F05', 'fnome' => 'MegaSupplies', 'indirizzo' => 'Via Napoli 5, Napoli'],
        ['fid' => 'F06', 'fnome' => 'GreenTech', 'indirizzo' => 'Via Palermo 6, Palermo'],
    ],
    'pezzi' => [
        ['pid' => 'P01', 'pnome' => 'Bullone', 'colore' => 'rosso'],
        ['pid' => 'P02', 'pnome' => 'Vite', 'colore' => 'blu'],
        ['pid' => 'P03', 'pnome' => 'Dado', 'colore' => 'rosso'],
        ['pid' => 'P04', 'pnome' => 'Rivetto', 'colore' => 'verde'],
        ['pid' => 'P05', 'pnome' => 'Molla', 'colore' => 'blu'],
        ['pid' => 'P06', 'pnome' => 'Guarnizione', 'colore' => 'rosso'],
        ['pid' => 'P07', 'pnome' => 'Cuscinetto', 'colore' => 'verde'],
        ['pid' => 'P08', 'pnome' => 'Cavo', 'colore' => 'blu'],
        ['pid' => 'P09', 'pnome' => 'Resistore', 'colore' => 'rosso'],
        ['pid' => 'P10', 'pnome' => 'Condensatore', 'colore' => 'verde'],
    ],
    'catalogo' => [
        // Acme (F01): TUTTI P01-P10
        ['fid' => 'F01', 'pid' => 'P01', 'costo' => 10.5],
        ['fid' => 'F01', 'pid' => 'P02', 'costo' => 5.0],
        ['fid' => 'F01', 'pid' => 'P03', 'costo' => 8.5],
        ['fid' => 'F01', 'pid' => 'P04', 'costo' => 6.0],
        ['fid' => 'F01', 'pid' => 'P05', 'costo' => 7.2],
        ['fid' => 'F01', 'pid' => 'P06', 'costo' => 9.0],
        ['fid' => 'F01', 'pid' => 'P07', 'costo' => 12.0],
        ['fid' => 'F01', 'pid' => 'P08', 'costo' => 4.5],
        ['fid' => 'F01', 'pid' => 'P09', 'costo' => 15.0],
        ['fid' => 'F01', 'pid' => 'P10', 'costo' => 8.5],
        
        // WidgetCorp (F02): TUTTI P01-P10
        ['fid' => 'F02', 'pid' => 'P04', 'costo' => 6.8],
        ['fid' => 'F02', 'pid' => 'P05', 'costo' => 7.1],
        ['fid' => 'F02', 'pid' => 'P06', 'costo' => 8.8],
        ['fid' => 'F02', 'pid' => 'P01', 'costo' => 11.0],
        ['fid' => 'F02', 'pid' => 'P02', 'costo' => 5.2],
        ['fid' => 'F02', 'pid' => 'P03', 'costo' => 8.2],
        ['fid' => 'F02', 'pid' => 'P07', 'costo' => 11.5],
        ['fid' => 'F02', 'pid' => 'P08', 'costo' => 4.2],
        ['fid' => 'F02', 'pid' => 'P09', 'costo' => 16.0],
        ['fid' => 'F02', 'pid' => 'P10', 'costo' => 9.2],
        
        // Supplies Inc (F03): TUTTI P01-P10
        ['fid' => 'F03', 'pid' => 'P07', 'costo' => 13.0],
        ['fid' => 'F03', 'pid' => 'P08', 'costo' => 3.9],
        ['fid' => 'F03', 'pid' => 'P09', 'costo' => 15.0],
        ['fid' => 'F03', 'pid' => 'P10', 'costo' => 10.0],
        ['fid' => 'F03', 'pid' => 'P01', 'costo' => 9.8],
        ['fid' => 'F03', 'pid' => 'P02', 'costo' => 4.5],
        ['fid' => 'F03', 'pid' => 'P03', 'costo' => 8.2],
        ['fid' => 'F03', 'pid' => 'P04', 'costo' => 5.8],
        ['fid' => 'F03', 'pid' => 'P05', 'costo' => 6.8],
        ['fid' => 'F03', 'pid' => 'P06', 'costo' => 8.5],
    ]
];

function getMockDB() {
    global $mockData;
    return $mockData;
}

// ENDPOINT 1: Pezzi con fornitori
$app->get('/api/1', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $color = $params['color'] ?? null;
        $limit = (int)($params['limit'] ?? 1000);
        
        $result = [];
        foreach ($data['pezzi'] as $pezzo) {
            $hasFornitori = false;
            foreach ($data['catalogo'] as $cat) {
                if ($cat['pid'] === $pezzo['pid']) {
                    $hasFornitori = true;
                    break;
                }
            }
            
            if ($hasFornitori) {
                if (!$color || $pezzo['colore'] === $color) {
                    $result[] = ['pnome' => $pezzo['pnome'], 'colore' => $pezzo['colore']];
                }
            }
        }
        
        $result = array_slice($result, 0, $limit);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 2: Fornitori che forniscono ogni pezzo
$app->get('/api/2', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $result = [];
        
        foreach ($data['fornitori'] as $fornitore) {
            $fornisce_tutti = true;
            
            foreach ($data['pezzi'] as $pezzo) {
                $ha_questo_pezzo = false;
                
                foreach ($data['catalogo'] as $cat) {
                    if ($cat['fid'] === $fornitore['fid'] && $cat['pid'] === $pezzo['pid']) {
                        $ha_questo_pezzo = true;
                        break;
                    }
                }
                
                if (!$ha_questo_pezzo) {
                    $fornisce_tutti = false;
                    break;
                }
            }
            
            if ($fornisce_tutti) {
                $result[] = ['fnome' => $fornitore['fnome']];
            }
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 3: Fornitori con pezzi di colore specificato
$app->get('/api/3', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $color = $params['color'] ?? 'rosso';
        $result = [];
        
        $pezzi_colore = array_filter($data['pezzi'], fn($p) => $p['colore'] === $color);
        
        foreach ($data['fornitori'] as $fornitore) {
            $fornisce_tutti = true;
            
            foreach ($pezzi_colore as $pezzo) {
                $ha_questo_pezzo = false;
                
                foreach ($data['catalogo'] as $cat) {
                    if ($cat['fid'] === $fornitore['fid'] && $cat['pid'] === $pezzo['pid']) {
                        $ha_questo_pezzo = true;
                        break;
                    }
                }
                
                if (!$ha_questo_pezzo) {
                    $fornisce_tutti = false;
                    break;
                }
            }
            
            if ($fornisce_tutti && count($pezzi_colore) > 0) {
                $result[] = ['fnome' => $fornitore['fnome']];
            }
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 4: Pezzi forniti da un fornitore in esclusiva
$app->get('/api/4', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $supplier = $params['supplier'] ?? 'Acme';
        $result = [];
        
        $fornitore_id = null;
        foreach ($data['fornitori'] as $f) {
            if ($f['fnome'] === $supplier) {
                $fornitore_id = $f['fid'];
                break;
            }
        }
        
        if (!$fornitore_id) {
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
        
        $pezzi_fornitore = [];
        foreach ($data['catalogo'] as $cat) {
            if ($cat['fid'] === $fornitore_id) {
                $pezzi_fornitore[$cat['pid']] = true;
            }
        }
        
        foreach ($pezzi_fornitore as $pid => $val) {
            $esclusivo = true;
            foreach ($data['catalogo'] as $cat) {
                if ($cat['pid'] === $pid && $cat['fid'] !== $fornitore_id) {
                    $esclusivo = false;
                    break;
                }
            }
            
            if ($esclusivo) {
                foreach ($data['pezzi'] as $p) {
                    if ($p['pid'] === $pid) {
                        $result[] = ['pnome' => $p['pnome']];
                        break;
                    }
                }
            }
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 5: Fornitori con costo sopra media
$app->get('/api/5', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $minPercentage = (float)($params['min_percentage'] ?? 0);
        $result = [];
        
        $costi_medi = [];
        foreach ($data['pezzi'] as $pezzo) {
            $costi = array_filter($data['catalogo'], fn($c) => $c['pid'] === $pezzo['pid']);
            if (!empty($costi)) {
                $media = array_sum(array_column($costi, 'costo')) / count($costi);
                $costi_medi[$pezzo['pid']] = $media * (1 + $minPercentage / 100);
            }
        }
        
        $fornitori_trovati = [];
        foreach ($data['catalogo'] as $cat) {
            if (isset($costi_medi[$cat['pid']]) && $cat['costo'] > $costi_medi[$cat['pid']]) {
                $fornitori_trovati[$cat['fid']] = true;
            }
        }
        
        foreach ($fornitori_trovati as $fid => $val) {
            foreach ($data['fornitori'] as $f) {
                if ($f['fid'] === $fid) {
                    $result[] = ['fid' => $fid, 'fnome' => $f['fnome']];
                    break;
                }
            }
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 6: Per ciascun pezzo, fornitori con costo massimo su quel pezzo
$app->get('/api/6', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $result = [];
        
        foreach ($data['pezzi'] as $pezzo) {
            $righe_pezzo = array_values(array_filter($data['catalogo'], fn($c) => $c['pid'] === $pezzo['pid']));
            if (empty($righe_pezzo)) {
                continue;
            }

            $maxCosto = max(array_column($righe_pezzo, 'costo'));

            foreach ($righe_pezzo as $cat) {
                if ($cat['costo'] === $maxCosto) {
                    $fornitore = null;
                    foreach ($data['fornitori'] as $f) {
                        if ($f['fid'] === $cat['fid']) {
                            $fornitore = $f;
                            break;
                        }
                    }

                    if ($fornitore) {
                        $result[] = [
                            'pid' => $pezzo['pid'],
                            'pnome' => $pezzo['pnome'],
                            'fnome' => $fornitore['fnome'],
                            'costo' => $cat['costo']
                        ];
                    }
                }
            }
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 7: Fornitori che forniscono SOLO pezzi rossi
$app->get('/api/7', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $result = [];

        foreach ($data['fornitori'] as $fornitore) {
            $forniture = array_values(array_filter($data['catalogo'], fn($c) => $c['fid'] === $fornitore['fid']));
            if (empty($forniture)) {
                continue;
            }

            $soloRossi = true;
            foreach ($forniture as $cat) {
                $pezzo = null;
                foreach ($data['pezzi'] as $p) {
                    if ($p['pid'] === $cat['pid']) {
                        $pezzo = $p;
                        break;
                    }
                }

                if (!$pezzo || $pezzo['colore'] !== 'rosso') {
                    $soloRossi = false;
                    break;
                }
            }

            if ($soloRossi) {
                $result[] = ['fid' => $fornitore['fid']];
            }
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 8: Fornitori che forniscono un pezzo rosso E uno verde
$app->get('/api/8', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $result = [];

        foreach ($data['fornitori'] as $fornitore) {
            $haRosso = false;
            $haVerde = false;

            foreach ($data['catalogo'] as $cat) {
                if ($cat['fid'] !== $fornitore['fid']) {
                    continue;
                }

                foreach ($data['pezzi'] as $p) {
                    if ($p['pid'] === $cat['pid']) {
                        if ($p['colore'] === 'rosso') {
                            $haRosso = true;
                        }
                        if ($p['colore'] === 'verde') {
                            $haVerde = true;
                        }
                        break;
                    }
                }
            }

            if ($haRosso && $haVerde) {
                $result[] = ['fid' => $fornitore['fid']];
            }
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 9: Fornitori che forniscono un pezzo rosso O uno verde
$app->get('/api/9', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $result = [];

        foreach ($data['fornitori'] as $fornitore) {
            $trovato = false;

            foreach ($data['catalogo'] as $cat) {
                if ($cat['fid'] !== $fornitore['fid']) {
                    continue;
                }

                foreach ($data['pezzi'] as $p) {
                    if ($p['pid'] === $cat['pid'] && ($p['colore'] === 'rosso' || $p['colore'] === 'verde')) {
                        $trovato = true;
                        break 2;
                    }
                }
            }

            if ($trovato) {
                $result[] = ['fid' => $fornitore['fid']];
            }
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 10: Pezzi forniti da almeno due fornitori
$app->get('/api/10', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $result = [];

        foreach ($data['pezzi'] as $pezzo) {
            $fornitoriPid = [];
            foreach ($data['catalogo'] as $cat) {
                if ($cat['pid'] === $pezzo['pid']) {
                    $fornitoriPid[$cat['fid']] = true;
                }
            }

            if (count($fornitoriPid) >= 2) {
                $result[] = ['pid' => $pezzo['pid']];
            }
        }

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Route principale - documentation
$app->get('/', function (Request $request, Response $response) {
    $data = [
        'message' => 'API REST - Slim Framework',
        'endpoints' => [
            '/api/1' => 'Pezzi con fornitori',
            '/api/2' => 'Fornitori che forniscono ogni pezzo',
            '/api/3' => 'Fornitori con pezzi di colore specificato',
            '/api/4' => 'Pezzi forniti da un fornitore in esclusiva',
            '/api/5' => 'Fornitori con costo sopra media',
            '/api/6' => 'Per ciascun pezzo, fornitori con costo massimo',
            '/api/7' => 'Fornitori che forniscono solo pezzi rossi',
            '/api/8' => 'Fornitori che forniscono un pezzo rosso e uno verde',
            '/api/9' => 'Fornitori che forniscono un pezzo rosso o uno verde',
            '/api/10' => 'Pezzi forniti da almeno due fornitori'
        ]
    ];
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
