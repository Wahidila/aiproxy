.PHONY: build run clean

# Build for Linux (VPS deployment)
build:
	CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -ldflags="-s -w" -o ai-token-proxy .

# Build for current OS (development)
build-local:
	go build -o ai-token-proxy .

# Run locally
run:
	go run .

# Clean build artifacts
clean:
	rm -f ai-token-proxy

# Build and copy to VPS (adjust SSH details)
deploy: build
	@echo "Binary built: ai-token-proxy (Linux amd64)"
	@echo "Copy to VPS: scp ai-token-proxy user@your-vps:/path/to/app/"
	@echo "Then on VPS: systemctl restart ai-token-proxy"
