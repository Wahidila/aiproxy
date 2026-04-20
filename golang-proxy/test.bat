@echo off
echo ========================================
echo   AI Token Proxy - Test Suite
echo ========================================
echo.

set API_KEY=sk-3065566f552790dc3aa8cc4761d82d93f2b92217a6939fb42c2e56c5a06f9fb3
set BASE=http://localhost:8080

echo [Test 1] Health Check (no auth)
curl -s %BASE%/v1/health
echo.
echo.

echo [Test 2] No Auth - expect 401
curl -s %BASE%/v1/models
echo.
echo.

echo [Test 3] Models (with auth)
curl -s -H "Authorization: Bearer %API_KEY%" %BASE%/v1/models
echo.
echo.

echo [Test 4] Chat Completions (non-streaming)
curl -s -X POST %BASE%/v1/chat/completions -H "Authorization: Bearer %API_KEY%" -H "Content-Type: application/json" -d "{\"model\":\"deepseek-3.2\",\"messages\":[{\"role\":\"user\",\"content\":\"say ok\"}],\"max_tokens\":5}"
echo.
echo.

echo [Test 5] Chat Completions (streaming)
curl -s -X POST %BASE%/v1/chat/completions -H "Authorization: Bearer %API_KEY%" -H "Content-Type: application/json" -d "{\"model\":\"deepseek-3.2\",\"messages\":[{\"role\":\"user\",\"content\":\"say ok\"}],\"max_tokens\":5,\"stream\":true}"
echo.
echo.

echo [Test 6] Free Tier Restriction (premium model - expect 403)
curl -s -X POST %BASE%/v1/chat/completions -H "Authorization: Bearer %API_KEY%" -H "Content-Type: application/json" -d "{\"model\":\"claude-opus-4.6\",\"messages\":[{\"role\":\"user\",\"content\":\"hi\"}],\"max_tokens\":5}"
echo.
echo.

echo [Test 7] Verify DB - Recent token_usages
D:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -u root ai_token_dashboard -e "SELECT id, model, input_tokens, output_tokens, cost_idr FROM token_usages ORDER BY id DESC LIMIT 5;"
echo.

echo [Test 8] Verify DB - Recent wallet_transactions
D:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -u root ai_token_dashboard -e "SELECT id, type, amount, balance_after, LEFT(description,50) as description FROM wallet_transactions ORDER BY id DESC LIMIT 5;"
echo.

echo ========================================
echo   Response Time Comparison
echo ========================================
echo.

echo [Golang] Response time:
curl -s -w "  Total: %%{time_total}s\n" -X POST %BASE%/v1/chat/completions -H "Authorization: Bearer %API_KEY%" -H "Content-Type: application/json" -d "{\"model\":\"deepseek-3.2\",\"messages\":[{\"role\":\"user\",\"content\":\"say ok\"}],\"max_tokens\":5}" -o NUL
echo.

echo [Laravel] Response time:
curl -s -w "  Total: %%{time_total}s\n" -X POST http://ai-token-dashboard.test/api/v1/chat/completions -H "Authorization: Bearer %API_KEY%" -H "Content-Type: application/json" -d "{\"model\":\"deepseek-3.2\",\"messages\":[{\"role\":\"user\",\"content\":\"say ok\"}],\"max_tokens\":5}" -o NUL
echo.

echo ========================================
echo   All tests complete!
echo ========================================
pause
