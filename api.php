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

// Dati mock per il testing
$mockData = [
    'fornitori' => [
        ['fid' => 'F01', 'fnome' => 'Acme', 'indirizzo' => 'Via Roma 1'],
        ['fid' => 'F02', 'fnome' => 'WidgetCorp', 'indirizzo' => 'Via Milano 2'],
        ['fid' => 'F03', 'fnome' => 'Supplies Inc', 'indirizzo' => 'Via Torino 3'],
    ],
    'pezzi' => [
        ['pid' => 'P01', 'pnome' => 'Bullone', 'colore' => 'rosso'],
        ['pid' => 'P02', 'pnome' => 'Vite', 'colore' => 'blu'],
        ['pid' => 'P03', 'pnome' => 'Dado', 'colore' => 'rosso'],
        ['pid' => 'P04', 'pnome' => 'Rivetto', 'colore' => 'verde'],
    ],
    'catalogo' => [
        ['fid' => 'F01', 'pid' => 'P01', 'costo' => 10.5],
        ['fid' => 'F01', 'pid' => 'P02', 'costo' => 5.0],
        ['fid' => 'F01', 'pid' => 'P03', 'costo' => 8.5],
        ['fid' => 'F02', 'pid' => 'P01', 'costo' => 11.0],
        ['fid' => 'F02', 'pid' => 'P03', 'costo' => 7.5],
        ['fid' => 'F02', 'pid' => 'P04', 'costo' => 6.0],
        ['fid' => 'F03', 'pid' => 'P02', 'costo' => 4.5],
        ['fid' => 'F03', 'pid' => 'P04', 'costo' => 6.5],
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

// ENDPOINT 6: Fornitori con tutti i pezzi
$app->get('/api/6', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $result = [];
        
        foreach ($data['fornitori'] as $fornitore) {
            $num_pezzi = 0;
            
            foreach ($data['catalogo'] as $cat) {
                if ($cat['fid'] === $fornitore['fid']) {
                    $num_pezzi++;
                }
            }
            
            if ($num_pezzi === count($data['pezzi'])) {
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

// ENDPOINT 7: Costo medio per pezzo
$app->get('/api/7', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $color = $params['color'] ?? null;
        $sort = strtoupper($params['sort'] ?? 'ASC');
        $limit = (int)($params['limit'] ?? 1000);
        
        $result = [];
        
        foreach ($data['pezzi'] as $pezzo) {
            if ($color && $pezzo['colore'] !== $color) {
                continue;
            }
            
            $costi = array_filter($data['catalogo'], fn($c) => $c['pid'] === $pezzo['pid']);
            if (!empty($costi)) {
                $media = array_sum(array_column($costi, 'costo')) / count($costi);
                $result[] = [
                    'pnome' => $pezzo['pnome'],
                    'colore' => $pezzo['colore'],
                    'costo_medio' => round($media, 2),
                    'num_fornitori' => count($costi)
                ];
            }
        }
        
        usort($result, function($a, $b) use ($sort) {
            if ($sort === 'DESC') {
                return $b['costo_medio'] <=> $a['costo_medio'];
            }
            return $a['costo_medio'] <=> $b['costo_medio'];
        });
        
        $result = array_slice($result, 0, $limit);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 8: Pezzi più costosi
$app->get('/api/8', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $color = $params['color'] ?? null;
        $limit = (int)($params['limit'] ?? 10);
        $minPrice = (float)($params['min_price'] ?? 0);
        
        $result = [];
        
        foreach ($data['pezzi'] as $pezzo) {
            if ($color && $pezzo['colore'] !== $color) {
                continue;
            }
            
            $costi = array_filter($data['catalogo'], fn($c) => $c['pid'] === $pezzo['pid']);
            if (!empty($costi)) {
                $max_costo = max(array_column($costi, 'costo'));
                if ($max_costo >= $minPrice) {
                    $result[] = [
                        'pnome' => $pezzo['pnome'],
                        'colore' => $pezzo['colore'],
                        'costo_massimo' => $max_costo
                    ];
                }
            }
        }
        
        usort($result, fn($a, $b) => $b['costo_massimo'] <=> $a['costo_massimo']);
        $result = array_slice($result, 0, $limit);
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 9: Numero fornitori per pezzo
$app->get('/api/9', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $color = $params['color'] ?? null;
        $minSuppliers = (int)($params['min_suppliers'] ?? 0);
        
        $result = [];
        
        foreach ($data['pezzi'] as $pezzo) {
            if ($color && $pezzo['colore'] !== $color) {
                continue;
            }
            
            $num_fornitori = count(array_filter($data['catalogo'], fn($c) => $c['pid'] === $pezzo['pid']));
            
            if ($num_fornitori >= $minSuppliers) {
                $result[] = [
                    'pnome' => $pezzo['pnome'],
                    'colore' => $pezzo['colore'],
                    'num_fornitori' => $num_fornitori
                ];
            }
        }
        
        usort($result, fn($a, $b) => $b['num_fornitori'] <=> $a['num_fornitori']);
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ENDPOINT 10: Fornitori con lista pezzi
$app->get('/api/10', function (Request $request, Response $response) {
    try {
        $data = getMockDB();
        $params = $request->getQueryParams();
        $supplier = $params['supplier'] ?? null;
        $color = $params['color'] ?? null;
        
        $result = [];
        
        foreach ($data['fornitori'] as $fornitore) {
            if ($supplier && $fornitore['fnome'] !== $supplier) {
                continue;
            }
            
            $pezzi = [];
            $num_pezzi = 0;
            
            foreach ($data['catalogo'] as $cat) {
                if ($cat['fid'] === $fornitore['fid']) {
                    foreach ($data['pezzi'] as $p) {
                        if ($p['pid'] === $cat['pid']) {
                            if (!$color || $p['colore'] === $color) {
                                $pezzi[] = $p['pnome'] . ' (' . $p['colore'] . ')';
                                $num_pezzi++;
                            }
                            break;
                        }
                    }
                }
            }
            
            $result[] = [
                'fnome' => $fornitore['fnome'],
                'fid' => $fornitore['fid'],
                'pezzi' => implode(', ', $pezzi),
                'num_pezzi' => $num_pezzi
            ];
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
            '/api/6' => 'Fornitori con tutti i pezzi',
            '/api/7' => 'Costo medio per pezzo',
            '/api/8' => 'Pezzi più costosi',
            '/api/9' => 'Numero fornitori per pezzo',
            '/api/10' => 'Fornitori con lista pezzi'
        ]
    ];
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
